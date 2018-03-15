<?php

namespace Application\Provider;

use Application\Authentication\Adapter\ShibUser;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Entity\Ldap\People;
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

    private $roles;

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
        if (! ($identity = $this->authenticationService->getIdentity())) {
            return [];
        }

        if ($this->roles !== null) {
            return $this->roles;
        }

        switch (true) {
            case isset($identity['ldap']):
                /** @var People $userData */
                $userData = $identity['ldap'];
                $id = $userData->getSupannEmpId();
                $username = $userData->getSupannAliasLogin();
                $mail = $userData->getMail();
                break;
            case isset($identity['shib']):
                /** @var ShibUser $userData */
                $userData = $identity['shib'];
                $id = $userData->getId();
                $username = $userData->getUsername();
                $mail = $userData->getEmail();
                break;
            default:
                throw new RuntimeException("Aucune donnée d'identité LDAP ni Shibboleth disponible");
        }

        $roles = array_merge([],
            $this->getRolesFromAutreCompteUtilisateur($mail),
            $this->getRolesFromActeur($id),
            $this->getRolesFromIndividuRole($id),
            $this->getRolesFromDoctorant($username));

        // suppression des doublons en comparant le __toString() de chaque Role
        $this->roles = array_unique($roles, SORT_STRING);

        return $this->roles;
    }

    /**
     * @param string $mail
     * @return Role[]
     */
    private function getRolesFromAutreCompteUtilisateur($mail)
    {
        /** @var Utilisateur[] $utilisateurs */
        $utilisateurs = $this->utilisateurService->getRepository()->findBy(['email' => $mail]);

        $roles = [];
        foreach ($utilisateurs as $utilisateur) {
            $roles = array_merge($roles, $utilisateur->getRoles()->toArray());
        }

        return $roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Acteur.
     *
     * @param string $id
     * @return Role[]
     */
    private function getRolesFromActeur($id)
    {
        $acteurs = $this->acteurService->getRepository()->findBySourceCodeIndividu($id);

        // pour l'instant on ne considère pas tous les types d'acteur
        $acteurs = array_filter($acteurs, function(Acteur $a) {
            return $a->getRole()->getCode() === Role::CODE_DIRECTEUR_THESE;
        });

        return array_map(function(Acteur $a) {
            return $a->getRole();
        }, $acteurs);
    }

    /** Rôle découlant de la présence dans IndividuRoles.
     *
     * @param string $id
     * @return Role[]
     */
    private function getRolesFromIndividuRole($id)
    {
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
     * Rôle découlant de la présence de l'utilisateur dans la table Doctorant.
     *
     * @param string $id
     * @return Role[]
     */
    private function getRolesFromDoctorant($id)
    {
        /**
         * NB: Un doctorant a la possibilité de s'authentifier :
         * - avec son numéro étudiant (Doctorant::sourceCode),
         * - avec son persopass (DoctorantCompl::persopass), seulement après qu'il l'a saisi sur la page d'identité de la thèse.
         */
        $username = $id;
        // todo: solution provisoire!
        $etablissement = 'UCN';
        try {
            $doctorant = $this->doctorantService->getRepository()->findOneByUsernameAndEtab($username, $etablissement);
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants ont été trouvés avec le même username: " . $username);
        }

        if (! $doctorant) {
            return [];
        }

        $role = $this->roleService->getRepository()->findRoleDoctorantForEtab($doctorant->getEtablissement());

        return [$role];
    }
}