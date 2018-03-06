<?php

namespace Application\Provider;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Entity\Db\UniteRechercheIndividu;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Ldap\People;
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

        /** @var People $people */
        $people = $identity['ldap'];

        $roles = array_merge([],
            $this->getRolesFromActeur($people),
            $this->getRolesFromIndividuRole($people),
            $this->getRolesFromDoctorant($people));

        // suppression des doublons en comparant le __toString() de chaque Role
        $this->roles = array_unique($roles, SORT_STRING);

        return $this->roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Acteur.
     *
     * @param People $people
     * @return Role[]
     */
    private function getRolesFromActeur(People $people)
    {
        $acteurs = $this->acteurService->getRepository()->findBySourceCodeIndividu($people->getSupannEmpId());

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
     *  @param People $people
     *  @return Role[]
     */
    private function getRolesFromIndividuRole(People $people) {
        $result = $this->ecoleDoctoraleService->getRepository()->findMembresBySourceCodeIndividu($people->getSupannEmpId());
        $individu = $result[0]->getIndividu();
        $roles = $this->roleService->getIndividuRolesByIndividu($individu);

        return array_map(function(IndividuRole $role) {
            return $role->getRole();
        }, $roles);
    }

    /**
     * Rôle découlant de la présence de l'utilisateur dans la table Doctorant.
     *
     * @param People $people
     * @return Role[]
     */
    private function getRolesFromDoctorant(People $people)
    {
        /**
         * NB: Un doctorant a la possibilité de s'authentifier :
         * - avec son numéro étudiant (Doctorant::sourceCode),
         * - avec son persopass (DoctorantCompl::persopass), seulement après qu'il l'a saisi sur la page d'identité de la thèse.
         */
        $username = $people->getSupannAliasLogin();
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