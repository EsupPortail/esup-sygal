<?php

namespace Soutenance\Assertion\HDR;

use Acteur\Entity\Db\ActeurHDR;
use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareTrait;
use DateInterval;
use DateTime;
use HDR\Entity\Db\HDR;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Soutenance\Entity\Etat;
use Soutenance\Entity\PropositionHDR;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Validation\Entity\Db\TypeValidation;
use Validation\Service\ValidationHDR\ValidationHDRServiceAwareTrait;

class PropositionHDRAssertion implements  AssertionInterface {
    use UserContextServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;

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
        /** Recuperation de la proposition **/
        /** @var HDR $hdr */
        $hdr = $resource;
        $proposition = $hdr->getCurrentProposition();

        $role = $this->userContextService->getSelectedIdentityRole();
        return $this->computeValeur($role, $proposition, $privilege);

    }

    /**
     * @param RoleInterface|null $role
     * @param PropositionHDR|null $proposition
     * @param $privilege
     * @return bool
     *
     */
    public function computeValeur(RoleInterface $role = null, ?PropositionHDR $proposition = null, $privilege = null) : bool
    {
        $hdr = $proposition->getHDR();
        $sursis = ($proposition)?$proposition->hasSursis():false;
        $dateValidationMax = ($proposition->getDate())?DateTime::createFromFormat('d/m/Y',$proposition->getDate()->format('d/m/Y'))->sub(new DateInterval('P2M')):null;
        $dateCurrent = new DateTime();

        /** @var Role $identityRole */
        if ($role === null) {
            $identityRole = $this->userContextService->getSelectedIdentityRole();
            $role = $identityRole->getCode();
            $structure = $identityRole->getStructure();
        } else {
            $structure = $role->getStructure();
            $role = $role->getCode();
        }
        
        $individu = $this->userContextService->getIdentityDb()->getIndividu();

        $candidat = $hdr->getCandidat()->getIndividu();

        /** @var ActeurHDR[] $acteurs */
        $garts = $hdr->getActeursByRoleCode(Role::CODE_HDR_GARANT)->toArray();
        $garants = [];
        foreach ($garts as $acteur) $garants[] = $acteur->getIndividu();

        $rapporteursJ = $hdr->getActeursByRoleCode(Role::CODE_RAPPORTEUR_JURY);
        $rapporteursA = $hdr->getActeursByRoleCode(Role::CODE_RAPPORTEUR_ABSENT);
        $acteurs = array_merge($rapporteursJ->toArray(), $rapporteursA->toArray());
        $rapporteurs = [];
        foreach ($acteurs as $acteur) $rapporteurs[] = $acteur->getIndividu();

        $hdrEtablissementStructure = $hdr->getEtablissement()->getStructure();

        switch ($privilege) {
            case PropositionPrivileges::PROPOSITION_VISUALISER:
                switch ($role) {
                    case Role::CODE_ADMIN_TECH:
                        return true;
                    case Role::CODE_GEST_HDR :
                        return $structure === $hdrEtablissementStructure;
                    case Role::CODE_RESP_UR :
                    case Role::CODE_GEST_UR :
                        return $structure === $hdr->getUniteRecherche()->getStructure();
                    case Role::CODE_HDR_CANDIDAT :
                        return $candidat->getId() === $individu->getId();
                    case Role::CODE_HDR_GARANT :
                        return (array_search($individu, $garants) !== false);
                    case Role::CODE_RAPPORTEUR_JURY :
                    case Role::CODE_RAPPORTEUR_ABSENT :
                        return (array_search($individu, $rapporteurs) !== false);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_MODIFIER:
                /** REMARQUE : une fois que l'unite de recherche ou la/le gestionnaire HDR a validé, on ne peut plus modifier la proposition **/
                $validations_UNITE  = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr);
                $validations_GEST_HDR = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $hdr);
                if ($validations_UNITE || $validations_GEST_HDR) return false;

                switch ($role) {
                    case Role::CODE_HDR_CANDIDAT :
                        return $candidat->getId() === $individu->getId();
                    case Role::CODE_HDR_GARANT :
                        return (array_search($individu, $garants) !== false);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_MODIFIER_GESTION:
                switch ($role) {
                    case Role::CODE_ADMIN_TECH :
                        return true;
                    case Role::CODE_GEST_HDR :
                        return ($hdr->getEtablissement()->getStructure() === $structure);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR:
                $validations_ACTEUR = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $hdr);
                switch ($role) {
                    case Role::CODE_HDR_CANDIDAT :
                        $isCandidat = $candidat->getId() === $individu->getId();
                        $dateOk = ($sursis OR ($dateCurrent <= $dateValidationMax));
                        foreach ($validations_ACTEUR as $validation) {
                            if($validation->getIndividu() === $candidat) return false;
                        }
                        return ($isCandidat AND $dateOk);
                    case Role::CODE_HDR_GARANT :
                        $isGarant = (array_search($individu, $garants) !== false);
                        $dateOk = ($sursis OR ($dateCurrent <= $dateValidationMax));
                        $garant = $garants[0];
                        foreach ($validations_ACTEUR as $validation) {
                            if($validation->getIndividu() === $garant) return false;
                        }
                        return ($isGarant AND $dateOk);
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_UR:
                switch ($role) {
                    case Role::CODE_RESP_UR :
                        $validationsArray = [];
                        $validationsArray[$hdr->getCandidat()->getIndividu()->getId()] = [];
                        /** @var ActeurHDR $garant */
                        foreach ($hdr->getActeursByRoleCode(Role::CODE_HDR_GARANT) as $garant) $validationsArray[$garant->getIndividu()->getId()] = [];
                        $validations_ACTEUR = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_PROPOSITION_SOUTENANCE, $hdr);
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
                        $validations_UNITE  = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr);
                        return !$validations_UNITE && $ok && $structure === $hdr->getUniteRecherche()->getStructure();
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_VALIDER_BDD:
                switch ($role) {
                    case Role::CODE_GEST_HDR:
                        $validations_UNITE  = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr);
                        $validations_GEST_HDR    = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $hdr);
                        return !$validations_GEST_HDR && $validations_UNITE && $structure === $hdrEtablissementStructure;
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE:
                if ($proposition->getEtat()->getCode() !== Etat::EN_COURS_EXAMEN && $proposition->getEtat()->getCode() !== Etat::ETABLISSEMENT) return false;
                switch ($role) {
                    case Role::CODE_GEST_HDR:
                        $validations_GEST_HDR  = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $hdr);
                        return (!empty($validations_GEST_HDR) && $structure === $hdrEtablissementStructure);
                    case Role::CODE_RESP_UR :
                        $validations_UNITE  = $this->getValidationHDRService()->getRepository()->findValidationByCodeAndHDR(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $hdr);
                        return (!empty($validations_UNITE) && $structure === $hdr->getUniteRecherche()->getStructure());
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_PRESIDENCE:
                switch($role) {
                    case Role::CODE_GEST_HDR:
                        return $structure === $hdrEtablissementStructure;
                    default:
                        return false;
                }
            case PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS:
                switch ($role) {
                    case Role::CODE_ADMIN_TECH :
                        return true;
                    case Role::CODE_GEST_HDR :
                        return $structure === $hdr->getEtablissement()->getStructure();
                    default:
                        return false;
                }
            default :
                return false;
        }
    }
}