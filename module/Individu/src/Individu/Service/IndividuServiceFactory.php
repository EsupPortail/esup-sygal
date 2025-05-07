<?php

namespace Individu\Service;

use Application\Service\Pays\PaysService;
use Application\SourceCodeStringHelper;
use Psr\Container\ContainerInterface;

class IndividuServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuService
    {
        $service = new IndividuService();

        /** @var SourceCodeStringHelper $sourceCodeHelper */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var PaysService $paysService */
        $paysService = $container->get(PaysService::class);
        $service->setPaysService($paysService);

        return $service;
    }
}