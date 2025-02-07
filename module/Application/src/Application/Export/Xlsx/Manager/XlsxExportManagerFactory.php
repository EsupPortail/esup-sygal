<?php

namespace Application\Export\Xlsx\Manager;

use Application\Export\Xlsx\Writer\XlsxWriterManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class XlsxExportManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): XlsxExportManager
    {
        $xlsxExportConfig = $container->get('Config')['xlsx_export'];

        $manager =  new XlsxExportManager($container);

        // config des exports
        $config = $xlsxExportConfig['config'] ?? [];
        $manager->setConfig($config);

        // factory et alias
        $factories = $xlsxExportConfig['factories'] ?? [];
        $aliases = $xlsxExportConfig['aliases'] ?? [];
        Assert::isEmpty(
            $diff = array_diff(array_keys($factories), $aliases),
            "Chacune des classes d'exports suivantes doit avoir un alias : " . implode(', ', $diff)
        );
        foreach ($factories as $className => $factoryName) {
            $manager->setFactory($className, $factoryName);
            Assert::notEmpty(
                $config[$className] ?? [],
                "Une clé de config '$className' doit exister et fournir la config de la classe d'export du même nom"
            );
        }
        foreach ($aliases as $alias => $className) {
            $manager->setAlias($alias, $className);
        }

        // writer manager
        /** @var \Application\Export\Xlsx\Writer\XlsxWriterManager $writerManager */
        $writerManager = $container->get(XlsxWriterManager::class);
        $manager->setXlsxWriterManager($writerManager);

        return $manager;
    }
}