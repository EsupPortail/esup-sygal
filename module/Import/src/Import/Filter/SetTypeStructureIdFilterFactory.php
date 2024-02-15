<?php

namespace Import\Filter;

use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Structure\Service\Structure\StructureService;

class SetTypeStructureIdFilterFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $filter = new SetTypeStructureIdFilter();

        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $sourceService = $container->get(SourceService::class);
        $filter->setSourceService($sourceService);
        $filter->setStructureService($structureService);

        return $filter;
    }
}