<?php

namespace Application\Service;

use Application\Authentication\Storage\AppStorage;
use Application\Entity\UserWrapperFactoryAwareTrait;
use Candidat\Entity\Db\Candidat;
use Doctorant\Entity\Db\Doctorant;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\Role;
use These\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use UnicaenAuthentification\Entity\Ldap\People;
use UnicaenAuthentification\Entity\Shibboleth\ShibUser;
use UnicaenAuthentification\Service\UserContext as BaseUserContextService;
use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * @method Role getSelectedIdentityRole()
 */
class UserContextService extends BaseUserContextService
{
    use IndividuServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserWrapperFactoryAwareTrait;

    /**
     * Si le rôle sélectionné correspond à celui de doctorant,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleDoctorant()
    {
        return $this->_getSelectedRoleForCode(Role::CODE_DOCTORANT);
    }

    /**
     * Si le rôle sélectionné correspond à celui de la Maison du doctorat,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return Role|null
     */
    public function getSelectedRoleBDD()
    {
        return $this->_getSelectedRoleForCode(Role::CODE_BDD);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleBU()
    {
        return $this->_getSelectedRoleForCode(Role::CODE_BU);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleDirecteurThese(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_DIRECTEUR_THESE);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleGarantHDR(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_HDR_GARANT);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleCodirecteurThese(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_CODIRECTEUR_THESE);
    }

    public function getSelectedRolePotentielDirecteurThese(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_ADMISSION_DIRECTEUR_THESE);
    }

    public function getSelectedRolePotentielCodirecteurThese(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_ADMISSION_CODIRECTEUR_THESE);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleEcoleDoctorale(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_RESP_ED) ?: $this->_getSelectedRoleForCode(Role::CODE_GEST_ED);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleUniteRecherche(): ?Role
    {
        return $this->_getSelectedRoleForCode(Role::CODE_RESP_UR) ?: $this->_getSelectedRoleForCode(Role::CODE_GEST_UR);
    }

    /**
     * @return Role|null
     */
    public function getSelectedRoleAdministrateur()
    {
        return $this->_getSelectedRoleForCode(Role::CODE_ADMIN);
    }

    /**
     * Si le rôle sélectionné correspond à celui de candidat HDR,
     * retourne le rôle en question, sinon retourne null.
     *
     * @return RoleInterface|null
     */
    public function getSelectedRoleCandidatHDR()
    {
        return $this->_getSelectedRoleForCode(Role::CODE_HDR_CANDIDAT);
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
     * @param string $code
     * @return null|Role
     */
    protected function _getSelectedRoleForCode($code)
    {
        /** @var Role $role */
        $role = $this->getSelectedIdentityRole();
        if (!$role || $role->getCode() !== $code) {
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
     * Retourne les données concernant l'utilisateur Shibboleth connecté.
     *
     * @return ShibUser|null
     */
    public function getIdentityShib()
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity['shib'];
    }

    /**
     * Retourne les données concernant l'utilisateur connecté, ssi son rôle correspondant à celui de doctorant.
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
     * Retourne les données concernant l'utilisateur connecté, ssi son rôle correspondant à celui de candidat HDR.
     *
     * @return \Candidat\Entity\Db\Candidat|null
     */
    public function getIdentityCandidatHDR(): Candidat|null
    {
        if (! $identity = $this->getIdentity()) {
            return null;
        }

        return $identity[AppStorage::KEY_CANDIDAT_HDR];
    }

    /**
     * Retourne l'individu correspondant à l'utilisateur connecté, le cas échéant.
     *
     * @return Individu|null
     */
    public function getIdentityIndividu(): ?Individu
    {
        $userWrapper = $this->createUserWrapperFromIdentity();
        if ($userWrapper === null) {
            return null;
        }

        if ($userWrapper->getIndividu()) {
            return $userWrapper->getIndividu();
        }

        if ($userWrapper->getSupannId() === null) {
            return null;
        }

        $etablissement = null;
        $domaineEtab = $userWrapper->getDomainFromEppn();
        if ($domaineEtab) {
            $etablissement = $this->getEtablissementService()->getRepository()->findOneByDomaine($domaineEtab);
        }
        if ($etablissement === null) {
            $etablissement = $this->getEtablissementService()->getRepository()->fetchEtablissementInconnu();
        }

        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($userWrapper->getSupannId(), $etablissement);

        return $this->individuService->getRepository()->findOneBySourceCode($sourceCode);
    }

    /**
     * @return UserWrapper
     */
    public function getIdentityUserWrapper()
    {
        return $this->createUserWrapperFromIdentity();
    }

    /**
     * @return UserWrapper
     */
    private function createUserWrapperFromIdentity()
    {
        if (!$this->getIdentity()) {
            return null;
        }

        try {
            $userWrapper = $this->userWrapperFactory->createInstanceFromIdentity($this->getIdentity());
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return null;
        }

        return $userWrapper;
    }

    /**
     * Teste si la structure sur laquelle porte le profil courant de l'utilisateur est compatible avec la thèse spécifiée.
     *
     * @param These $these
     * @return bool
     */
    public function isStructureDuRoleRespecteeForThese(These $these)
    {
        $role = $this->getSelectedIdentityRole();

        if($role->getCode() === Role::CODE_GEST_HDR) return false;

        if ($role->isTheseDependant()) {
            if ($role->isDoctorant()) {
                $utilisateurEstAuteurDeLaThese = $these->getDoctorant()->getId() === $this->getIdentityDoctorant()->getId();
                return $utilisateurEstAuteurDeLaThese;
            }elseif ($role->isActeurDeThese()) {
                if ($individu = $this->getIdentityIndividu()) {
                    return $these->hasActeurWithRole($individu, $role->getCode());
                }
                return false;
            }
        }elseif($role->isHDRDependant()){
            return false;
        }

        elseif ($role->isStructureDependant()) {
            if ($role->isEtablissementDependant()) {
                // On ne voit que les thèses de son établissement.
                return $these->getEtablissement()->getStructure() === $role->getStructure();
            }
            elseif ($role->isEcoleDoctoraleDependant()) {
                // On ne voit que les thèses concernant son ED.
                return $these->getEcoleDoctorale()->getStructure() === $role->getStructure();
            }
            elseif ($role->isUniteRechercheDependant()) {
                // On ne voit que les thèses concernant son UR.
                return $these->getUniteRecherche()->getStructure() === $role->getStructure();
            }
        }

        return true;
    }

    /**
     * Teste si la structure sur laquelle porte le profil courant de l'utilisateur est compatible avec l'HDR spécifiée.
     *
     * @param HDR $hdr
     * @return bool
     */
    public function isStructureDuRoleRespecteeForHDR(HDR $hdr)
    {
        $role = $this->getSelectedIdentityRole();

        if($role->getCode() === Role::CODE_BDD) return false;

        if ($role->isHDRDependant()) {
            if ($role->isCandidatHDR()) {
                return $hdr->getCandidat()->getId() === $this->getIdentityCandidatHDR()->getId();
            }elseif ($role->isActeurDeHDR()) {
                if ($individu = $this->getIdentityIndividu()) {
                    return $hdr->hasActeurWithRole($individu, $role->getCode());
                }
                return false;
            }
        }elseif($role->isTheseDependant()){
            return false;
        }

        elseif ($role->isStructureDependant()) {
            if ($role->isEtablissementDependant()) {
                // On ne voit que les HDR de son établissement.
                return $hdr->getEtablissement()->getStructure() === $role->getStructure();
            }
            elseif ($role->isEcoleDoctoraleDependant()) {
                // On ne voit que les HDR concernant son ED.
                return $hdr->getEcoleDoctorale()->getStructure() === $role->getStructure();
            }
            elseif ($role->isUniteRechercheDependant()) {
                // On ne voit que les HDR concernant son UR.
                return $hdr->getUniteRecherche()->getStructure() === $role->getStructure();
            }
        }

        return true;
    }
}