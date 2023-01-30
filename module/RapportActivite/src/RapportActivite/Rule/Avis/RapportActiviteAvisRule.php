<?php

namespace RapportActivite\Rule\Avis;

use Application\Entity\Db\Role;
use Application\Rule\RuleInterface;
use InvalidArgumentException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Service\AvisServiceAwareTrait;

/**
 * Application de diverses règles métier concernant la création d'un avis sur un rapport d'activité.
 * @deprecated
 */
class RapportActiviteAvisRule implements RuleInterface
{
    use RapportActiviteOperationRuleAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use AvisServiceAwareTrait;

    /**
     * Listes des (codes de) types d'avis attendus.
     * Cf. UNICAEN_AVIS_TYPE.CODE
     */
    private array $avisTypesCodesOrdered = [
        0 => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST,
        1 => RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED,
    ];

    /**
     * @inheritDoc
     */
    public function execute()
    {

    }

    /**
     * @return AvisType[]
     */
    public function findAllSortedAvisTypes(): array
    {
        return $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);
    }

    /**
     * Injecte dans le rapport spécifié les données indiquant si un avis peut être saisi.
     * @deprecated wfrule
     */
    public function injectRapportAvisPossible(RapportActivite $rapport)
    {
        $rapportAvisPossible = null;

        $avisTypeDispo = $this->findExpectedAvisTypeForRapport($rapport);
        if ($avisTypeDispo !== null) {
            $rapportAvisPossible = new RapportActiviteAvis();
            $rapportAvisPossible
                ->setRapportActivite($rapport)
                ->setAvis((new Avis())->setAvisType($avisTypeDispo));
        }

        $rapport->setRapportAvisPossible($rapportAvisPossible);
    }

    /**
     * @param \UnicaenAvis\Entity\Db\AvisType $avisType
     * @param \Application\Entity\Db\Role $role
     * @return bool
     * @deprecated Rempl&cé par {@see \RapportActivite\Rule\Operation\RapportActiviteOperationRule::isOperationAllowedByRole()}
     */
    public function isAvisTypeMatchingRole(AvisType $avisType, Role $role): bool
    {
        if ($role->isEcoleDoctoraleDependant()) {
            switch ($avisType->getCode()) {
                case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST:
                    $expectedCodes = [Role::CODE_GEST_ED, Role::CODE_RESP_ED];
                    break;
                case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED:
                    $expectedCodes = [Role::CODE_RESP_ED];
                    break;
                default:
                    throw new InvalidArgumentException("Type d'avis possible inattendu : " . $avisType->getCode());
            }

            return in_array($role->getCode(), $expectedCodes);
        }

        return true;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @return bool
     * @deprecated wfrule
     */
    public function isNextAvisValeurCompatible(RapportActiviteAvis $rapportActiviteAvis): bool
    {
        $nextRapportAvis = $this->findRapportAvisAfter($rapportActiviteAvis);
        if ($nextRapportAvis === null) {
            return true;
        }

        $avisValeur = RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED_VALEUR_INCOMPLET;

        return $nextRapportAvis->getAvis()->getAvisValeur()->getCode() === $avisValeur;
    }

    /**
     * Recherche l'éventuel avis fourni après celui spécifié.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     * @deprecated wfrule
     */
    public function findRapportAvisAfter(RapportActiviteAvis $rapportActiviteAvis): ?RapportActiviteAvis
    {
        $rapport = $rapportActiviteAvis->getRapportActivite();

        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);
        $nextAvisType = null;
        $found = false;
        foreach ($allSortedAvisTypes as $avisType) {
            if ($found) {
                $nextAvisType = $avisType;
                break;
            }
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $avisType) {
                $found = true;
            }
        }

        if ($nextAvisType !== null) {
            return $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType($rapport, $nextAvisType);
        }

        return null;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActivite $rapportActivite
     * @return bool
     * @deprecated wfrule
     */
    public function isMostRecentAvisValeurCompatible(RapportActivite $rapportActivite): bool
    {
        $dernierAvis = $this->findMostRecentRapportAvisForRapport($rapportActivite);
        if ($dernierAvis === null) {
            return true;
        }

        return $dernierAvis->getAvis()->getAvisValeur()->getValeurBool() === true;
    }

    /**
     * Retourne le type de l'avis précédant le dernier avis apporté sur le rapport spécifié,
     * ou `null` si un seul ou aucun avis n'a été apporté.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findPreviousAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        $last = $this->findMostRecentAvisTypeForRapport($rapport);
        if ($last === null) {
            return null;
        }

        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);

        $prev = null;
        foreach ($allSortedAvisTypes as $avisType) {
            $rapportActiviteAvis = $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $last) {
                break;
            }
            $prev = $avisType;
        }

        return $prev;
    }

    /**
     * Recherche l'éventuel avis fourni avant celui spécifié.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function findRapportAvisBefore(RapportActiviteAvis $rapportActiviteAvis): ?RapportActiviteAvis
    {
        $rapport = $rapportActiviteAvis->getRapportActivite();

        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);
        $previousAvisType = null;
        foreach ($allSortedAvisTypes as $avisType) {
            if ($rapportActiviteAvis->getAvis()->getAvisType() === $avisType) {
                break;
            }
            $previousAvisType = $avisType;
        }

        if ($previousAvisType !== null) {
            return $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType($rapport, $previousAvisType);
        }

        return null;
    }

    /**
     * Retourne l'avis le plus récent apporté sur le rapport spécifié,
     * ou `null` si aucun avis n'a été apporté.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     * @deprecated wfrule
     */
    public function findMostRecentRapportAvisForRapport(RapportActivite $rapport): ?RapportActiviteAvis
    {
        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);

        $prevRapportActiviteAvis = null;
        foreach ($allSortedAvisTypes as $avisType) {
            $rapportActiviteAvis = $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis === null) {
                break;
            }
            $prevRapportActiviteAvis = $rapportActiviteAvis;
        }

        return $prevRapportActiviteAvis;
    }

    /**
     * Retourne le type du dernier avis apporté sur le rapport spécifié,
     * ou `null` si aucun avis n'a été apporté.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     * @deprecated wfrule
     */
    public function findMostRecentAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);

        $last = null;
        foreach ($allSortedAvisTypes as $avisType) {
            $rapportActiviteAvis = $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType($rapport, $avisType);
            if ($rapportActiviteAvis === null) {
                break;
            }
            $last = $avisType;
        }

        return $last;
    }

    /**
     * Recherche le prochain type d'avis disponible/possible pour le rapport spécifié.
     *
     * ATTENTION ! Lors du fetch des rapports :
     * - Les relations suivantes doivent avoir été sélectionnées : 'rapportAvis->avis->avisType' ;
     * - L'orderBy 'avisType.ordre' doit avoir été utilisé.
     *
     * @param \RapportActivite\Entity\Db\RapportActivite $rapport
     * @return \UnicaenAvis\Entity\Db\AvisType|null
     * @deprecated Rempl&cé par {@see \RapportActivite\Rule\Operation\RapportActiviteOperationRule::findNextExpectedOperation()}
     */
    public function findExpectedAvisTypeForRapport(RapportActivite $rapport): ?AvisType
    {
        $allSortedAvisTypes = $this->avisService->findAvisTypesByCodes($this->avisTypesCodesOrdered);

        /** @var string[] $avisTypesCodesApportes */
        $avisTypesCodesApportes = array_map(
            fn(RapportActiviteAvis $ra) => $ra->getAvis()->getAvisType()->getCode(),
            $rapport->getRapportAvis()->toArray()
        );

        foreach ($allSortedAvisTypes as $avisType) {
            if (!in_array($avisType->getCode(), $avisTypesCodesApportes)) {
                return $avisType;
            }
        }

        return null;
    }

}