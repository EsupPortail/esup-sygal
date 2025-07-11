<?php

namespace Application\Service\AnneeUniv;

use Psr\Container\ContainerInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class AnneeUnivServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AnneeUnivService
    {
        /** @var \UnicaenParametre\Service\Parametre\ParametreService $parametreService */
        $parametreService = $container->get(ParametreService::class);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get('doctrine.connection.orm_default');

        $service = new AnneeUnivService(
            $parametreService->getParametreByCode('ANNEE_UNIV', 'SPEC_DATE_BASCULE')->getValeur(),
            $parametreService->getParametreByCode('ANNEE_UNIV', 'SPEC_ANNEE_UNIV_DATE_DEBUT')->getValeur(),
            $parametreService->getParametreByCode('ANNEE_UNIV', 'SPEC_ANNEE_UNIV_DATE_FIN')->getValeur()
        );
        $service->setConnection($connection);

        return $service;
    }
}