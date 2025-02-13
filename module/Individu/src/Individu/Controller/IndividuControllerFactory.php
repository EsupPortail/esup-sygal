<?php

namespace Individu\Controller;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Admission\Service\Inscription\InscriptionService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Candidat\Service\CandidatService;
use Structure\Service\Structure\StructureService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Doctorant\Service\DoctorantService;
use Individu\Form\IndividuForm;
use Individu\Service\IndividuService;
use Individu\Service\Search\IndividuSearchService;
use Psr\Container\ContainerInterface;

class IndividuControllerFactory {

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuController
    {
        $controller = new IndividuController();

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $controller->setIndividuService($individuService);

        /** @var \Application\Service\Utilisateur\UtilisateurService $utilisateurService */
        $utilisateurService = $container->get(UtilisateurService::class);
        $controller->setUtilisateurService($utilisateurService);

        /** @var IndividuForm $individuForm */
        $individuForm = $container->get('FormElementManager')->get(IndividuForm::class);
        $controller->setIndividuForm($individuForm);

        /** @var \Acteur\Service\ActeurThese\ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $controller->setActeurTheseService($acteurService);

        /** @var ActeurHDRService $acteurService */
        $acteurService = $container->get(ActeurHDRService::class);
        $controller->setActeurHDRService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get('RoleService');
        $controller->setApplicationRoleService($roleService);

        /** @var \Structure\Service\Structure\StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $controller->setStructureService($structureService);

        /** @var \Doctorant\Service\DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $controller->setDoctorantService($doctorantService);

        /** @var CandidatService $candidatService */
        $candidatService = $container->get(CandidatService::class);
        $controller->setCandidatService($candidatService);

        /** @var IndividuSearchService $searchService */
        $searchService = $container->get(IndividuSearchService::class);
        $controller->setSearchService($searchService);

        /** @var \Application\Service\UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);
        $controller->setUserContextService($userContextService);

        /** @var InscriptionService $inscriptionService */
        $inscriptionService = $container->get(InscriptionService::class);
        $controller->setInscriptionService($inscriptionService);

        return $controller;
    }
}