<?php

namespace Individu\Service;

use Application\SourceCodeStringHelper;
use Psr\Container\ContainerInterface;

class IndividuServiceFactory
{
    public function __invoke(ContainerInterface $container): IndividuService
    {
        $service = new IndividuService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}