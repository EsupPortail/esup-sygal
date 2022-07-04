<?php

namespace Formation\Service\Session\Search;

use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class SessionSearchServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SessionSearchService
    {
        $service = new SessionSearchService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        /** @var \Formation\Entity\Db\Repository\FormationRepository $formationRepository */
        $formationRepository = $em->getRepository(Formation::class);
        $service->setFormationRepository($formationRepository);

        /** @var \Formation\Entity\Db\Repository\SessionRepository $sessionRepository */
        $sessionRepository = $em->getRepository(Session::class);
        $service->setSessionRepository($sessionRepository);

        /** @var \Formation\Entity\Db\Repository\EtatRepository $etatRepository */
        $etatRepository = $em->getRepository(Etat::class);
        $service->setEtatRepository($etatRepository);

        /** @var \Structure\Service\Etablissement\EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}