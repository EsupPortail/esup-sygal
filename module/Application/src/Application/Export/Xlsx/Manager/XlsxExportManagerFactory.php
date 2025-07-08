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
        $xlsxExportConfig = $container->get('Config')['xlsx_export_manager'];

        $manager =  new XlsxExportManager($container);

        // exports et leur factory+alias
        $factories = $xlsxExportConfig['factories'] ?? [];
        $aliases = $xlsxExportConfig['aliases'] ?? [];
        $exports = $xlsxExportConfig['exports'] ?? [];
        foreach ($exports as $className => $config) {
            Assert::keyExists($factories, $className, "Une factory doit être fournie pour l'export '$className'");
            Assert::inArray($className, $aliases, "Un alias doit être fourni pour l'export '$className'");
        }
        $manager->setExports($exports);
        foreach ($factories as $className => $factoryName) {
            $manager->setFactory($className, $factoryName);
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