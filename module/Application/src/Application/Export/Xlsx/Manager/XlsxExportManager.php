<?php

namespace Application\Export\Xlsx\Manager;

use Application\Export\Xlsx\Writer\XlsxWriterManagerAwareTrait;
use Application\Export\Xlsx\XlsxExportInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Webmozart\Assert\Assert;

class XlsxExportManager extends AbstractPluginManager
{
    use XlsxWriterManagerAwareTrait;

    protected $instanceOf = XlsxExportInterface::class;
    protected array $config;

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function get($name, ?array $options = null): XlsxExportInterface
    {
        $options = $this->prepareOptionsForExport($name, $options);

        /** @var XlsxExportInterface $export */
        $export = parent::get($name, $options);

        $this->injectDependencies($export, $options);

        return $export;
    }

    protected function prepareOptionsForExport(string $name, ?array $options = null): array
    {
        $exportClassName = $this->aliases[$name] ?? $name;

        return array_merge_recursive($options ?? [], $this->config[$exportClassName]);
    }

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