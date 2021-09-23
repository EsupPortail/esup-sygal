<?php

namespace Application\Service\Individu;

use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class IndividuServiceFactory
{
    public function __invoke(ContainerInterface $container)
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