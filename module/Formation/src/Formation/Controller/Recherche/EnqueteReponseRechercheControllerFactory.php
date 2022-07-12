<?php

namespace Formation\Controller\Recherche;

use Formation\Entity\Db\EnqueteCategorie;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService;
use Psr\Container\ContainerInterface;

class EnqueteReponseRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EnqueteReponseRechercheController
    {
        $controller = new EnqueteReponseRechercheController();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        /** @var \Formation\Entity\Db\Repository\EnqueteQuestionRepository $repository */
        $repository = $em->getRepository(EnqueteQuestion::class);
        $controller->setEnqueteQuestionRepository($repository);

        /** @var \Formation\Entity\Db\Repository\EnqueteCategorieRepository $repository */
        $repository = $em->getRepository(EnqueteCategorie::class);
        $controller->setEnqueteCategorieRepository($repository);

        /** @var \Formation\Entity\Db\Repository\SessionRepository $repository */
        $repository = $em->getRepository(Session::class);
        $controller->setSessionRepository($repository);

        /** @var \Formation\Entity\Db\Repository\FormateurRepository $repository */
        $repository = $em->getRepository(Formateur::class);
        $controller->setFormateurRepository($repository);

        /** @var \Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService $searchService */
        $searchService = $container->get(EnqueteReponseSearchService::class);
        $controller->setSearchService($searchService);

        return $controller;
    }
}