<?php

namespace Application\Provider;

use Application\Service\Acteur\ActeurService;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Service\User as UserService;

/**
 * Application identity provider factory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class IdentityProviderFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var UserService $userService */
        $userService = $sl->get('zfcuser_user_service');

        /** @var ActeurService $acteurService */
        $acteurService = $sl->get(ActeurService::class);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $sl->get(DoctorantService::class);

        /** @var EcoleDoctoraleService $edService */
        $edService = $sl->get(EcoleDoctoraleService::class);

        /** @var UniteRechercheService $urService */
        $urService = $sl->get(UniteRechercheService::class);

        /** @var RoleService $roleService */
        $roleService = $sl->get(RoleService::class);

        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $sl->get('UtilisateurService');

        $service = new IdentityProvider();
        $service->setAuthenticationService($userService->getAuthService());
        $service->setActeurService($acteurService);
        $service->setDoctorantService($doctorantService);
        $service->setEcoleDoctoraleService($edService);
        $service->setUniteRechercheService($urService);
        $service->setRoleService($roleService);
        $service->setUtilisateurService($utilisateurService);

        return $service;
    }
}