<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative;

use Psr\Container\ContainerInterface;
use SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade\ImportFacade;

class InscriptionAdministrativeResourceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAdministrativeResource
    {
        $resource = new InscriptionAdministrativeResource();

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $container->get('inscription_resource_logger');
        $resource->setLogger($logger);

        /** @var \SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade\ImportFacade $importFacade */
        $importFacade = $container->get(ImportFacade::class);
        $resource->setImportFacade($importFacade);

        return $resource;
    }
}
