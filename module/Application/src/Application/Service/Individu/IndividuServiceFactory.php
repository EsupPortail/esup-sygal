<?php

namespace Application\Service\Individu;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class IndividuServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $service = new IndividuService();

        /**
         * @var \Application\Service\Source\SourceService $sourceService
         */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}