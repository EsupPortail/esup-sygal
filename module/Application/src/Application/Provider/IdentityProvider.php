<?php

namespace Application\Provider;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactory;
use Application\Service\Acteur\ActeurService;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use UnicaenAuth\Acl\NamedRole;
use UnicaenAuth\Provider\Identity\ChainableProvider;
use UnicaenAuth\Provider\Identity\ChainEvent;
use Zend\Authentication\AuthenticationService;

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

        $userWrapperFactory = new UserWrapperFactory();
        $this->userWrapper = $userWrapperFactory->createInstanceFromIdentity($identity);
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
        $acteurs = $this->acteurService->findAllActeursByUser($this->userWrapper);

        $acteursDirecteurThese = $this->acteurService->filterActeursDirecteurThese($acteurs);
        $acteursCoDirecteurThese = $this->acteurService->filterActeursCoDirecteurThese($acteurs);
        $acteursPresidentJury = $this->acteurService->filterActeursPresidentJury($acteurs);

        return array_map(
            function(Acteur $a) { return $a->getRole(); },
            array_merge($acteursDirecteurThese, $acteursCoDirecteurThese, $acteursPresidentJury)
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

        if ($individu !== null) {
            $individuRoles = $this->roleService->findIndividuRolesByIndividu($individu);
        } else {
            $id = $this->userWrapper->getSupannId();
            $pattern = $this->sourceCodeStringHelper->generateSearchPatternForAnyPrefix($id);
            $individuRoles = $this->roleService->findIndividuRolesByIndividuSourceCodePattern($pattern);
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
    private function getRolesFromDoctorant()
    {
        $doctorant = $this->doctorantService->findOneByUserWrapper($this->userWrapper);

        if (! $doctorant) {
            return [];
        }

        $role = $this->roleService->getRepository()->findRoleDoctorantForEtab($doctorant->getEtablissement());

        return [$role];
    }
}