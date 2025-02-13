<?php

namespace Application\Provider;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Entity\UserWrapperFactory;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Candidat\Service\CandidatService;
use Doctorant\Service\DoctorantService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Role\RoleService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use ZfcUser\Service\User as UserService;

/**
 * Application identity provider factory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class IdentityProviderFactory
{
    use EtablissementServiceLocateTrait;

    public function __invoke(ContainerInterface $container)
    {
        /** @var UserService $userService */
        $userService = $container->get('zfcuser_user_service');

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);

        /** @var ActeurHDRService $acteurHDRService */
        $acteurHDRService = $container->get(ActeurHDRService::class);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);

        /** @var CandidatService $candidatService */
        $candidatService = $container->get(CandidatService::class);

        /** @var EcoleDoctoraleService $edService */
        $edService = $container->get(EcoleDoctoraleService::class);

        /** @var UniteRechercheService $urService */
        $urService = $container->get(UniteRechercheService::class);

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);

        /** @var UtilisateurService $utilisateurService */
        $utilisateurService = $container->get('UtilisateurService');

        $etablissementService = $this->locateEtablissementService($container);

        /** @var UserWrapperFactory $userWrapperFactory */
        $userWrapperFactory = $container->get(UserWrapperFactory::class);

        $service = new IdentityProvider();
        $service->setAuthenticationService($userService->getAuthService());
        $service->setActeurTheseService($acteurService);
        $service->setActeurHDRService($acteurHDRService);
        $service->setDoctorantService($doctorantService);
        $service->setCandidatService($candidatService);
        $service->setEcoleDoctoraleService($edService);
        $service->setUniteRechercheService($urService);
        $service->setApplicationRoleService($roleService);
        $service->setUtilisateurService($utilisateurService);
        $service->setEtablissementService($etablissementService);
        $service->setUserWrapperFactory($userWrapperFactory);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}