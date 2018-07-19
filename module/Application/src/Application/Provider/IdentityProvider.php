<?php

namespace Application\Provider;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\UserWrapper;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Provider\Identity\ChainableProvider;
use UnicaenAuth\Provider\Identity\ChainEvent;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Service chargé de fournir tous les rôles que possède l'identité authentifiée.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class IdentityProvider implements ProviderInterface, ChainableProvider, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use RoleServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use EtablissementServiceAwareTrait;

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

        $this->userWrapper = UserWrapper::instFromIdentity($identity);
        if ($this->userWrapper === null) {
            return [];
        }

        $this->roles = array_merge([],
            $this->getRolesFromActeur(),
            $this->getRolesFromIndividuRole(),
            $this->getRolesFromDoctorant());

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
    private function getRolesFromActeur()
    {
        $id = $this->userWrapper->getSupannId();

        $acteurs = $this->acteurService->getRepository()->findBySourceCodeIndividu($id);

        // pour l'instant on ne considère pas tous les types d'acteur
        $acteurs = array_filter($acteurs, function(Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_DIRECTEUR_THESE;
        });

        return array_map(function(Acteur $a) {
            return $a->getRole();
        }, $acteurs);
    }

    /**
     * Rôles découlant de la présence dans IndividuRole.
     *
     * @return Role[]
     */
    private function getRolesFromIndividuRole()
    {
        $id = $this->userWrapper->getSupannId();

        $roles = $this->roleService->getIndividuRolesByIndividuSourceCode($id);

        usort($roles, function (IndividuRole $a, IndividuRole $b) {
            //filtre sur le type de structure
            if ($a->getRole()->getTypeStructureDependant() !== $b->getRole()->getTypeStructureDependant()) {
                return $a->getRole()->getTypeStructureDependant() > $b->getRole()->getTypeStructureDependant();
            } elseif ($a->getRole()->getStructure()->getLibelle() !== $a->getRole()->getStructure()->getLibelle()) {
                return $a->getRole()->getStructure()->getLibelle() > $b->getRole()->getStructure()->getLibelle();
            }
            return $a->getId() > $b->getId();
        });

        return array_map(function(IndividuRole $role) {
            return $role->getRole();
        }, $roles);
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Doctorant.
     *
     * @return Role[]
     */
    private function getRolesFromDoctorant()
    {
        $id = $this->userWrapper->getSupannId();
        $domaineEtab = $this->userWrapper->getDomainFromEppn();
        $etablissement = $this->getEtablissementService()->getRepository()->findOneByDomaine($domaineEtab);
        $sourceCode = $etablissement->prependPrefixTo($id);

        try {
            $doctorant = $this->doctorantService->getRepository()->findOneBySourceCode($sourceCode);
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants ont été trouvés avec le même source code: " . $sourceCode);
        }

        if (! $doctorant) {
            return [];
        }

        $role = $this->roleService->getRepository()->findRoleDoctorantForEtab($doctorant->getEtablissement());

        return [$role];
    }
}