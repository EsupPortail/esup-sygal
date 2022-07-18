<?php

namespace Formation\Service\EnqueteReponse\Search;

use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Formation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class EnqueteReponseSearchServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EnqueteReponseSearchService
    {
        $service = new EnqueteReponseSearchService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        /** @var \Formation\Entity\Db\Repository\FormationRepository $repository */
        $repository = $em->getRepository(Formation::class);
        $service->setFormationRepository($repository);

        /** @var \Formation\Entity\Db\Repository\EnqueteReponseRepository $repository */
        $repository = $em->getRepository(EnqueteReponse::class);
        $service->setEnqueteReponseRepository($repository);

        /** @var \Structure\Service\Etablissement\EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}