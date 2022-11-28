<?php

namespace Application\Provider;

use Application\Entity\Db\Role;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactoryAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Entity\Db\IndividuRole;
use Laminas\Authentication\AuthenticationService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenAuth\Provider\Identity\ChainableProvider;
use UnicaenAuth\Provider\Identity\ChainEvent;

/**
 * Service chargé de fournir tous les rôles que possède l'identité authentifiée.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class IdentityProvider implements ProviderInterface, ChainableProvider
{
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use RoleServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserWrapperFactoryAwareTrait;

    private $roles;

    /**
     * @var UserWrapper
     */
    private $userWrapper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     * @return IdentityProvider
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function injectIdentityRoles(ChainEvent $event)
    {
        $event->addRoles($this->getIdentityRoles());
    }

    /**
     * Collecte tous les rôles de l'utilisateur authentifié.
     *
     * @return Role[]
     */
    public function getIdentityRoles()
    {
        if (! $this->authenticationService->hasIdentity()) {
            return [];
        }

        if ($this->roles !== null) {
            return $this->roles;
        }

        /** @var array $identity */
        $identity = $this->authenticationService->getIdentity();

        try {
            $this->userWrapper = $this->userWrapperFactory->createInstanceFromIdentity($identity);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return [];
        }
        if ($this->userWrapper === null) {
            return [];
        }

        $roleAuthentifie = $this->roleService->getRepository()->findByCode('user')/*->setLibelle("coucou")*/;

        $this->roles = array_merge(
            [$roleAuthentifie],
            $this->getRolesFromActeur(),
            $this->getRolesFromIndividuRole(),
            $this->getRolesFromDoctorant()
        );

// Lignes mises en commentaire car revient à considérer le rôle "BdD UCN" identique au rôle "BdD URN" !
// La question est: pourquoi avoir fait ça ?
//        // suppression des doublons en comparant le __toString() de chaque Role
//        $this->roles = array_unique($this->roles, SORT_STRING);

        return $this->roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Acteur.
     *
     * @return Role[]
     */
    private function getRolesFromActeur(): array
    {
        $individu = $this->userWrapper->getIndividu();
        if ($individu === null) {
            return [];
        }

        $acteurs = $this->acteurService->getRepository()->findActeursForIndividu($individu);

        $acteursDirecteurThese = $this->acteurService->filterActeursDirecteurThese($acteurs);
        $acteursCoDirecteurThese = $this->acteurService->filterActeursCoDirecteurThese($acteurs);
        $acteursPresidentJury = $this->acteurService->filterActeursPresidentJury($acteurs);
        $acteursRapporteurJury = $this->acteurService->filterActeursRapporteurJury($acteurs);

        return array_map(
            function(Acteur $a) { return $a->getRole(); },
            array_merge($acteursDirecteurThese, $acteursCoDirecteurThese, $acteursPresidentJury, $acteursRapporteurJury)
        );
    }

    /**
     * Rôles découlant de la présence dans IndividuRole.
     *
     * @return Role[]
     */
    private function getRolesFromIndividuRole()
    {
        // peut-être disposons-nous de l'Individu (cas d'une authentification locale)
        $individu = $this->userWrapper->getIndividu();

        $individuRoles = [];
        if ($individu !== null) {
            $individuRoles = $this->roleService->findIndividuRolesByIndividu($individu);
        } else {
            $id = $this->userWrapper->getSupannId();
            if ($id) {
                $pattern = $this->sourceCodeStringHelper->generateSearchPatternForAnyPrefix($id);
                $individuRoles = $this->roleService->findIndividuRolesByIndividuSourceCodePattern($pattern);
            }
        }

        usort($individuRoles, IndividuRole::getComparisonFunction());

        $roles = array_map(function(IndividuRole $role) {
            return $role->getRole();
        }, $individuRoles);

        return $roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Doctorant.
     *
     * @return Role[]
     */
    private function getRolesFromDoctorant(): array
    {
        $doctorant = $this->doctorantService->findOneByUserWrapper($this->userWrapper);

        if (! $doctorant) {
            return [];
        }

        $role = $this->roleService->getRepository()
            ->findOneByCodeAndStructureConcrete(Role::CODE_DOCTORANT, $doctorant->getEtablissement());

        return [$role];
    }
}