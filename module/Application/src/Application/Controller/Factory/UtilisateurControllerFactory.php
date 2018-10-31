<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\ControllerManager;

class UtilisateurControllerFactory
{
    use IndividuServiceLocateTrait;

    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var EtablissementService $etablissementService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         * @var StructureService $structureService
         * @var EntityManager $entityManager
         * @var NotifierService $notifierService
         */
        $roleService = $sl->get('RoleService');
        $utilisateurService = $sl->get('UtilisateurService');
        $etablissementService = $sl->get('EtablissementService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $uniteService = $sl->get('UniteRechercheService');
        $structureService = $sl->get(StructureService::class);
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $entityManager = $sl->get('doctrine.entitymanager.orm_default');

        $controller = new UtilisateurController();
        $controller->setRoleService($roleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setIndividuService($this->locateIndividuService($sl));
        $controller->setUniteRechercheService($uniteService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);
        $controller->setNotifierService($notifierService);
        $controller->setEntityManager($entityManager);

        return $controller;
    }
}
