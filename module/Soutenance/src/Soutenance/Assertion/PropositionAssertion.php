<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class PropositionAssertion implements  AssertionInterface {
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;

    /**
     * !!!! Pour éviter l'erreur "Serialization of 'Closure' is not allowed"... !!!!
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    public function __invoke($page)
    {
        return true;
    }

    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        /** @var These $these */
        $these = $resource;
        $role = $this->userContextService->getSelectedIdentityRole()->getCode();
        $structure = $this->userContextService->getSelectedIdentityRole()->getStructure();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        $doctorant = $these->getDoctorant()->getIndividu();

        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        /** @var Acteur[] $acteurs */
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray());
        $directeurs = [];
        foreach ($acteurs as $acteur) $directeurs[] = $acteur->getIndividu();

//        if ($role === Role::CODE_ADMIN_TECH) return true;

        switch ($privilege) {
            case SoutenancePrivileges::SOUTENANCE_PROPOSITION_VISUALISER:
                switch ($role) {
                    case Role::CODE_ADMIN_TECH:
                        return true;
                        break;
                    case Role::CODE_BDD :
                        return $structure === $these->getEtablissement()->getStructure();
                        break;
                    case Role::CODE_ED :
                        return $structure === $these->getEcoleDoctorale()->getStructure();
                        break;
                    case Role::CODE_UR :
                        return $structure === $these->getUniteRecherche()->getStructure();
                        break;
                    case Role::CODE_DOCTORANT :
                        return $doctorant->getId() === $individu->getId();
                        break;
                    case Role::CODE_DIRECTEUR_THESE :
                    case Role::CODE_CODIRECTEUR_THESE :
                        return (array_search($individu, $directeurs) !== false);
                        break;
                    default:
                        return false;
                        break;
                }
            case SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER:
                switch ($role) {
                    case Role::CODE_DOCTORANT :
                        return $doctorant->getId() === $individu->getId();
                        break;
                    case Role::CODE_DIRECTEUR_THESE :
                    case Role::CODE_CODIRECTEUR_THESE :
                        return (array_search($individu, $directeurs) !== false);
                        break;
                    default:
                        return false;
                        break;
                }
            case SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ACTEUR:
                switch ($role) {
                    case Role::CODE_DOCTORANT :
                        return $doctorant->getId() === $individu->getId();
                        break;
                    case Role::CODE_DIRECTEUR_THESE :
                    case Role::CODE_CODIRECTEUR_THESE :
                        return (array_search($individu, $directeurs) !== false);
                        break;
                    default:
                        return false;
                        break;
                }
            case SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_UR:
                switch ($role) {
                    case Role::CODE_UR :
                        $validations_ACTEUR = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $these);
                        $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                        $nbDirs = count($these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE));
                        $nbCoDirs = count($these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE));
                        $nbActeur = 1 + $nbDirs + $nbCoDirs;
                        return !$validations_UNITE && count($validations_ACTEUR) === $nbActeur && $structure === $these->getUniteRecherche()->getStructure();
                        break;
                    default:
                        return false;
                        break;
                }
            case SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ED:
                switch ($role) {
                    case Role::CODE_ED :
                        $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                        $validations_ECOLE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                        return !$validations_ECOLE && $validations_UNITE && $structure === $these->getEcoleDoctorale()->getStructure();
                        break;
                    default:
                        return false;
                        break;
                }
            case SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_BDD:

                switch ($role) {
                    case Role::CODE_BDD :
                        $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                        $validations_ECOLE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                        $validations_BDD    = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
                        return !$validations_BDD && $validations_UNITE && $validations_ECOLE && $structure === $these->getEtablissement()->getStructure();
                        break;
                    default:
                        return false;
                        break;
                }
            default :
                return false;
                break;
        }
    }
}