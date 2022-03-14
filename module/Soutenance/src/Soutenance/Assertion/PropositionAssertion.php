<?php

namespace Soutenance\Assertion;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use DateInterval;
use DateTime;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

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
        $proposition = null;
        /** @var Proposition $p */
        foreach ($these->getPropositions() as $p) {
            if ($p->estNonHistorise()) {
                $proposition = $p;
                break;
            }
        }
        $sursis = ($proposition)?$proposition->hasSursis():false;
        $dateValidationMax = ($proposition->getDate())?DateTime::createFromFormat('d/m/Y',$proposition->getDate()->format('d/m/Y'))->sub(new DateInterval('P2M')):null;
        $dateCurrent = new DateTime();

        /** @var Role $identityRole */
        $identityRole = $this->userContextService->getSelectedIdentityRole();
        $role = $identityRole->getCode();
        $structure = $identityRole->getStructure();
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        $doctorant = $these->getDoctorant()->getIndividu();

        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        /** @var Acteur[] $acteurs */
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray());
        $directeurs = [];
        foreach ($acteurs as $acteur) $directeurs[] = $acteur->getIndividu();

//        if ($role === Role::CODE_ADMIN_TECH) return true;

        $theseEtablissementStructure = $these->getEtablissement()->getStructure();

        switch ($privilege) {
            case PropositionPrivileges::PROPOSITION_VISUALISER:
                switch ($role) {
                    case Role::CODE_ADMIN_TECH:
                        return true;
                    case Role::CODE_BDD :
                        return $structure === $theseEtablissementStructure;
                    case Role::CODE_RESP_ED :
                    case Role::CODE_GEST_ED :
                        return $structure === $these->getEcoleDoctorale()->getStructure();
                    case Role::CODE_RESP_UR :
                        return $structure === $these->getUniteRecherche()->getStructure();
                    case Role::CODE_DOCTORANT :
                        return $doctorant->getId() === $individu->getId();
                    case Role::CODE_DIRECTEUR_THESE :
                    case Role::CODE_CODIRECTEUR_THESE :
                        return (array_search($individu, $directeurs) !== false);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_MODIFIER:
                /** REMARQUE : une fois que l'unite de recherche, l'école doctorale ou le bureau des doctorats a validé, on ne peut plus modifier la proposition **/
                $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                $validations_ECOLE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                $validations_BUREAU = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
                if ($validations_UNITE || $validations_ECOLE || $validations_BUREAU) return false;

                switch ($role) {
                    case Role::CODE_DOCTORANT :
                        return $doctorant->getId() === $individu->getId();
                    case Role::CODE_DIRECTEUR_THESE :
                    case Role::CODE_CODIRECTEUR_THESE :
                        return (array_search($individu, $directeurs) !== false);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_MODIFIER_GESTION:
                switch ($role) {
                    case Role::CODE_ADMIN_TECH :
                        return true;
                    case Role::CODE_BDD :
                        return ($these->getEtablissement()->getStructure() === $identityRole->getStructure());
//                        $validations_ACTEUR = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $these);
//                        $validations_UNITE = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
//                        $nbDirs = count($these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE));
//                        $nbCoDirs = count($these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE));
//                        $nbActeur = 1 + $nbDirs + $nbCoDirs;
//                        return !$validations_UNITE && count($validations_ACTEUR) === $nbActeur && $structure === $theseEtablissementStructure;
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR:

                switch ($role) {
                    case Role::CODE_DOCTORANT :
                        $isDoctorant = $doctorant->getId() === $individu->getId();
                        $dateOk = ($sursis OR ($dateCurrent <= $dateValidationMax));
                        return ($isDoctorant AND $dateOk);
                    case Role::CODE_DIRECTEUR_THESE :
                    case Role::CODE_CODIRECTEUR_THESE :
                        $idDirecteur = (array_search($individu, $directeurs) !== false);
                        $dateOk = ($sursis OR ($dateCurrent <= $dateValidationMax));
                        return ($idDirecteur AND $dateOk);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_UR:
                switch ($role) {
                    case Role::CODE_RESP_UR :

                        $validationsArray = [];
                        $validationsArray[$these->getDoctorant()->getIndividu()->getId()] = [];
                        /** @var Acteur $directeur */
                        foreach ($these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE) as $directeur) $validationsArray[$directeur->getIndividu()->getId()] = [];
                        foreach ($these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE) as $directeur) $validationsArray[$directeur->getIndividu()->getId()] = [];
                        $validations_ACTEUR = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $these);
                        foreach ($validations_ACTEUR as $validation) {
                            $validationsArray[$validation->getIndividu()->getId()][] = $validation;
                        }
                        $ok = true;
                        foreach ($validationsArray as $id => $validations) {
                            if (empty($validations)) {
                                $ok = false;
                                break;
                            }
                        }
                        $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                        return !$validations_UNITE && $ok && $structure === $these->getUniteRecherche()->getStructure();
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_ED:
                switch ($role) {
                    case Role::CODE_RESP_ED :
                    case Role::CODE_GEST_ED :
                        $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                        $validations_ECOLE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                        return !$validations_ECOLE && $validations_UNITE && $structure === $these->getEcoleDoctorale()->getStructure();
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_BDD:

                switch ($role) {
                    case Role::CODE_BDD :
                        $validations_UNITE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these);
                        $validations_ECOLE  = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these);
                        $validations_BDD    = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these);
                        return !$validations_BDD && $validations_UNITE && $validations_ECOLE && $structure === $theseEtablissementStructure;
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_PRESIDENCE:
                switch($role) {
                    case Role::CODE_BDD :
                        return $structure === $theseEtablissementStructure;
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER:
                switch ($role) {
                    case Role::CODE_DOCTORANT :
                        $isDoctorant = $doctorant->getId() === $individu->getId();
                        $dateOk = ($sursis OR ($dateCurrent <= $dateValidationMax));
                        return ($isDoctorant AND $dateOk);
                    default:
                    return false;
                }
            case PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER:
                switch($role) {
                    case Role::CODE_ADMIN_TECH :
                        return true;
                    case Role::CODE_BDD :
                        return ($these->getEtablissement()->getStructure() === $identityRole->getStructure());
                    default :
                        return false;
                }
            default :
                return false;
        }
    }
}