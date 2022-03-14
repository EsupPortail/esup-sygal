<?php

namespace Import\Filter;

use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PrefixEtabColumnValueFilterFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $filter = new PrefixEtabColumnValueFilter();

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $filter->setSourceService($sourceService);

        return $filter;
    }
}