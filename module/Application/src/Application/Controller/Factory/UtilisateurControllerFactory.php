<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\Notification\NotificationService;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManager;
use UnicaenLdap\Service\People as LdapPeopleService;
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
         * @var LdapPeopleService $ldapPeopleService
         * @var EtablissementService $etablissementService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         * @var EntityManager $entityManager;
         * @var NotificationService $notificationService;
         */
        $roleService = $sl->get('RoleService');
        $ldapPeopleService  = $sl->get('LdapServicePeople');
        $utilisateurService = $sl->get('UtilisateurService');
        $etablissementService = $sl->get('EtablissementService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $uniteService = $sl->get('UniteRechercheService');
        $notificationService = $controllerManager->getServiceLocator()->get(NotificationService::class);
        $entityManager = $sl->get('doctrine.entitymanager.orm_default');

        $controller = new UtilisateurController();
        $controller->setRoleService($roleService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setIndividuService($this->locateIndividuService($sl));
        $controller->setUniteRechercheService($uniteService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setNotificationService($notificationService);
        $controller->setEntityManager($entityManager);

        return $controller;
    }
}
