<?php

namespace Application\Search\AnneeUniv;

use Application\Service\AnneeUniv\AnneeUnivService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnneeUnivSearchFilterFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AnneeUnivSearchFilter
    {
        $filter = new AnneeUnivSearchFilter();

        /** @var AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $filter->setAnneeUnivService($anneeUnivService);

        return $filter;
    }
}