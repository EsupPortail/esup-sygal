<?php

namespace Formation\Service\Inscription\Search;

use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\EtatRepository;
use Formation\Entity\Db\Repository\InscriptionRepository;
use Individu\Service\IndividuService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InscriptionSearchServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InscriptionSearchService
    {
        $service = new InscriptionSearchService();

        /** @var EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        /** @var InscriptionRepository $repository */
        $repository = $em->getRepository(Inscription::class);
        $service->setInscriptionRepository($repository);

        /** @var EtatRepository $etatRepository */
        $etatRepository = $em->getRepository(Etat::class);
        $service->setEtatRepository($etatRepository);

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        return $service;
    }
}