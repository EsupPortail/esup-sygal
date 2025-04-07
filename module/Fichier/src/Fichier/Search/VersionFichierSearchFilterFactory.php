<?php

namespace Fichier\Search;

use Fichier\Service\VersionFichier\VersionFichierService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class VersionFichierSearchFilterFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): VersionFichierSearchFilter
    {
        $filter = new VersionFichierSearchFilter();

        /** @var VersionFichierService $VersionFichierService */
        $versionFichierService = $container->get(VersionFichierService::class);
        $filter->setData($versionFichierService->getRepository()->findAll());

        return $filter;
    }
}