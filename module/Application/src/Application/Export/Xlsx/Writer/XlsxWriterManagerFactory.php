<?php

namespace Application\Export\Xlsx\Writer;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class XlsxWriterManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): XlsxWriterManager
    {
        $manager = new XlsxWriterManager($container);

        $factories = $container->get('Config')['xlsx_writer']['factories'] ?? [];
        Assert::notEmpty($factories, "Aucune factory spécifiée pour les writers xlsx");

        foreach ($factories as $className => $factoryName) {
            $manager->setFactory($className, $factoryName);
        }

        return $manager;
    }
}