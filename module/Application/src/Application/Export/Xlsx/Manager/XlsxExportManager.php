<?php

namespace Application\Export\Xlsx\Manager;

use Application\Export\Xlsx\AbstractXlsxExport;
use Application\Export\Xlsx\Writer\XlsxWriterManagerAwareTrait;
use Application\Export\Xlsx\XlsxExportDataProcessorInterface;
use Application\Export\Xlsx\XlsxExportDataProviderInterface;
use Application\Export\Xlsx\XlsxExportInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use UnicaenDbImport\Config\ColumnValueFilterConfigFactoryTrait;
use Webmozart\Assert\Assert;

class XlsxExportManager extends AbstractPluginManager
{
    use XlsxWriterManagerAwareTrait;
    use ColumnValueFilterConfigFactoryTrait;

    protected $instanceOf = XlsxExportInterface::class;
    protected array $exports;

    public function setExports(array $exports): void
    {
        $this->exports = $exports;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function get($name, ?array $options = null): XlsxExportInterface
    {
        $options = $this->prepareOptionsForExport($name, $options);

        /** @var XlsxExportInterface $export */
        $export = parent::get($name, $options);

        $this->injectDependencies($export, $options);

        return $export;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    protected function prepareOptionsForExport(string $name, ?array $options = null): array
    {
        $options = $options ?? [];

        $exportClassName = $this->aliases[$name] ?? $name;
        $exportConfig = $this->exports[$exportClassName];

        foreach ($exportConfig['data_tabs'] as $tabName => $tabConfig) {

            // remplacement de chaque nom de service de 'data provider' éventuel par l'instance du service
            $sourceType = $tabConfig['source_type'] ?? null;
            if ($sourceType === AbstractXlsxExport::SOURCE_TYPE_SERVICE) {
                Assert::keyExists($tabConfig, $key = 'source', "La clé %s est introuvable dans la config de l'onglet $tabName");
                $serviceName = $tabConfig['source'];
                Assert::true($this->creationContext->has($serviceName), "Le service spécifié dans la config de l'onglet $tabName (clé '$key') n'existe pas : " . $serviceName);
                $service = $this->creationContext->get($serviceName);
                Assert::isInstanceOf($service, XlsxExportDataProviderInterface::class,
                    "Le service spécifié dans la config de l'onglet $tabName (clé '$key') doit implémenter %s");

                $exportConfig['data_tabs'][$tabName]['source'] = $service;
            }

            // remplacement de chaque nom de 'data processor' éventuel par l'instance du service
            $dataProcessors = $tabConfig[$key = 'data_processors'] ?? [];
            foreach ($dataProcessors as $i => $config) {
                Assert::isArray($config, "Chaque data processor doit être spécifié au format tableau");
                Assert::keyExists($config, 'name', "La clé %s est obligatoire dans la config d'un data processor");
                $name = $config['name'];
                Assert::stringNotEmpty($name, "Le 'name' d'un data processor doit être un nom de service");
                Assert::true($this->creationContext->has($name), "Le service '$name' spécifié dans la config du data processor est introuvable");
                unset($config['name']);
                $service = $this->creationContext->build($name, $config);
                Assert::isInstanceOf($service, XlsxExportDataProcessorInterface::class,
                    "Le data processor '$name' spécifié dans la config de l'onglet $tabName doit implémenter %s");

                $exportConfig['data_tabs'][$tabName][$key][$i] = $service;
            }

            // remplacement de chaque spec de filtre de colonne par l'instance du filtre
            $columnValueFilters = $this->getColumnValueFilters($tabConfig, 'column_value_filters', $this->creationContext);
            $exportConfig['data_tabs'][$tabName]['column_value_filters'] = $columnValueFilters;
        }

        return array_merge_recursive($options, $exportConfig);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function injectDependencies(XlsxExportInterface $export, array $options): void
    {
        // injection du writer
        Assert::keyExists($options, 'writer_class', "Clé de config %s introuvable pour l'export " . get_class($export));
        $writerClass = $options['writer_class'];
        $writer = $this->xlsxWriterManager->get($writerClass);
        $export->setWriter($writer);
    }

    /**
     * @return XlsxExportInterface[]
     */
    public function getAll(): array
    {
        $instances = [];
        foreach ($this->aliases as $alias) {
            $instances[$alias] = $this->get($alias);
        }

        return $instances;
    }
}