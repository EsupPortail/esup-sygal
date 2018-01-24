<?php

namespace Application\Service;

use Application\Authentication\Storage\AppStorage;
use Application\Entity\Db\EcoleDoctoraleIndividu;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\UniteRechercheIndividu;
use Application\Entity\Db\Utilisateur;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use UnicaenAuth\Entity\Ldap\People;
use UnicaenAuth\Service\UserContext as BaseUserContextService;
use Zend\Permissions\Acl\Role\RoleInterface;

class UserContextService extends BaseUserContextService implements IndividuServiceAwareInterface
{
    use IndividuServiceAwareTrait;

    /**
     * @return RoleInterface|null
     */
    public function getSelectedIdentityRole()
    {
        $role = parent::getSelectedIdentityRole();

        /**
         * Si aucun rôle n'est sélectionné, on tente de sélectionner le 1er trouvé.
         */
        if (! $role) {
            $role = current($this->getSelectableIdentityRoles());
            if (!$this->isRoleValid($role)) {
                return null;
            }
            $this->setNextSelectedIdentityRole($role);
        }

        return $this->roleAsEntity($role);
    }

    private $roleAsEntityCache = [];

    /**
     * @param Role|RoleInterface|string $role
     * @return Role|null
     */
    private function roleAsEntity($role)
    {
        if (!$role) {
            return $role;
        }
        if ($role instanceof Role) {
            return $role;
        }
        if ($role instanceof RoleInterface) {
            $role = $role->getRoleId();
        }
        if (isset($this->roleAsEntityCache[$role])) {
            return $this->roleAsEntityCache[$role];
        }

        $this->roleAsEntityCache[$role] =
            $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $role]);

        return $this->roleAsEntityCache[$role];
    }

    /**
     * Si le rôle sélectionné correspond à celui de doctorant,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleDoctorant()
    {
        return $this->_getSelectedRoleForRoleId(Role::ROLE_ID_DOCTORANT);
    }

    /**
     * Si le rôle sélectionné correspond à celui de Bureau des doctorats,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleBDD()
    {
        return $this->_getSelectedRoleForRoleId(Role::ROLE_ID_BUREAU_DES_DOCTORATS);
    }

    /**
     * Si le rôle sélectionné correspond à celui de Bureau des doctorats,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleBU()
    {
        return $this->_getSelectedRoleForRoleId(Role::ROLE_ID_BIBLIO_UNIV);
    }

    /**
     * Si le rôle sélectionné correspond à celui de directeur de thèse,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return Role|null
     */
    public function getSelectedRoleDirecteurThese()
    {
        return $this->_getSelectedRoleForSourceCode(Role::SOURCE_CODE_DIRECTEUR_THESE);
    }

    /**
     * Si le rôle sélectionné correspond à celui de directeur d'ED,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleDirecteurEcoleDoctorale()
    {
        return $this->_getSelectedRoleForRoleId(Role::ROLE_ID_ECOLE_DOCT);
    }

    /**
     * Si le rôle sélectionné correspond à celui de directeur d'UR,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleDirecteurUniteRecherche()
    {
        return $this->_getSelectedRoleForRoleId(Role::ROLE_ID_UNITE_RECH);
    }

    /**
     * Si le rôle sélectionné correspond à celui spécifié,
     * retourne le rôle en question, sinon retourne null.
     *
     * @param string $roleId
     * @return null|RoleInterface
     */
    protected function _getSelectedRoleForRoleId($roleId)
    {
        $role = $this->getSelectedIdentityRole();
        if (!$role || $role->getRoleId() !== $roleId) {
            return null;
        }
        return $role;
    }
    /**
     * Si le rôle sélectionné correspond à celui spécifié,
     * retourne le rôle en question, sinon retourne null.
     *
     * @param string $sourceCode
     * @return null|RoleInterface
     */
    protected function _getSelectedRoleForSourceCode($sourceCode)
    {
        /** @var Role $role */
        $role = $this->getSelectedIdentityRole();
        if (!$role || $role->getSourceCode() !== $sourceCode) {
            return null;
        }
        return $role;
    }

    /**
     * Retourne les données concernant l'utilisateur connecté, issues de la table des utilisateurs en bdd.
     *
     * @return Utilisateur|null
     */
    public function getIdentityDb()
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity['db'];
    }

    /**
     * Retourne les données concernant l'utilisateur connecté, issues de l'annuaire LDAP.
     *
     * @return People|null
     */
    public function getIdentityLdap()
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity['ldap'];
    }

    /**
     * Retourne les données concernant l'utilisateur connecté, issues de la table des thésards, le cas échéant.
     *
     * @return Doctorant|null
     */
    public function getIdentityDoctorant()
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity[AppStorage::KEY_DOCTORANT];
    }

    /**
     * Retourne les données EcoleDoctoraleIndividu concernant l'utilisateur connecté..
     *
     * @return EcoleDoctoraleIndividu[]
     */
    public function getIdentityEcoleDoctoraleIndividu()
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity[AppStorage::KEY_ECOLE_DOCTORALE_INDIVIDU];
    }

    /**
     * Retourne les données UniteRechercheIndividu concernant l'utilisateur connecté..
     *
     * @return UniteRechercheIndividu[]
     */
    public function getIdentityUniteRechercheIndividu()
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity[AppStorage::KEY_UNITE_RECHERCHE_INDIVIDU];
    }

    /**
     * Retourne l'individu correspondant à l'utilisateur connecté, le cas échéant.
     *
     * @return Individu|null
     */
    public function getIdentityIndividu()
    {
        if (! $identity = $this->getIdentityLdap()) {
            return null;
        }

        /** @var Individu $individu */
        $individu = $this->individuService->getRepository()->findOneBy(['sourceCode' => $identity->getSupannEmpId()]);

        return $individu;
    }
}