<?php

namespace Formation\Service\Inscription\Search;

use Application\Service\AnneeUniv\AnneeUnivService;
use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\EtatRepository;
use Formation\Entity\Db\Repository\FormationRepository;
use Formation\Entity\Db\Repository\InscriptionRepository;
use Formation\Entity\Db\Repository\SessionRepository;
use Formation\Entity\Db\Session;
use Individu\Service\IndividuService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

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

        /** @var FormationRepository $formationRepository */
        $formationRepository = $em->getRepository(Formation::class);
        $service->setFormationRepository($formationRepository);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        /** @var InscriptionRepository $repository */
        $repository = $em->getRepository(Inscription::class);
        $service->setInscriptionRepository($repository);

        /** @var SessionRepository $repository */
        $repository = $em->getRepository(Session::class);
        $service->setSessionRepository($repository);

        /** @var EtatRepository $etatRepository */
        $etatRepository = $em->getRepository(Etat::class);
        $service->setEtatRepository($etatRepository);

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        /** @var AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $service->setAnneeUnivService($anneeUnivService);

        return $service;
    }
}