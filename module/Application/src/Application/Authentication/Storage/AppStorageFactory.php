<?php

namespace Application\Authentication\Storage;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Zend\ServiceManager\ServiceLocatorInterface;

class AppStorageFactory
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return AppStorage
     */
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $sl->get('UtilisateurService');

        /** @var DoctorantService $doctorantService */
        $doctorantService = $sl->get(DoctorantService::class);

        /** @var RoleService $roleService */
        $roleService = $sl->get(RoleService::class);


        $service = new AppStorage();
        $service->setUtilisateurService($utilisateurService);
        $service->setDoctorantService($doctorantService);
        $service->setRoleService($roleService);

        return $service;
    }
}