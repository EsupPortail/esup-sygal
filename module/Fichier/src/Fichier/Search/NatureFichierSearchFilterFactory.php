<?php

namespace Fichier\Search;

use Fichier\Service\NatureFichier\NatureFichierService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NatureFichierSearchFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NatureFichierSearchFilter
    {
        $filter = new NatureFichierSearchFilter();

        /** @var NatureFichierService $NatureFichierService */
        $natureFichierService = $container->get(NatureFichierService::class);
        $filter->setData($natureFichierService->getRepository()->findAll());

        return $filter;
    }
}