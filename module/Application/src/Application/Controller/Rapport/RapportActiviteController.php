<?php

namespace Application\Controller\Rapport;

use Application\Entity\AnneeUniv;
use Application\Entity\Db\Rapport;
use Application\Provider\Privilege\RapportPrivileges;

/**
 * @property \Application\Form\RapportActiviteForm $form
 */
class RapportActiviteController extends RapportController
{
    protected $routeName = 'rapport-activite';

    protected $privilege_TELEVERSER_TOUT = RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT;
    protected $privilege_TELEVERSER_SIEN = RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN;
    protected $privilege_SUPPRIMER_TOUT = RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT;
    protected $privilege_SUPPRIMER_SIEN = RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN;
    protected $privilege_TELECHARGER_TOUT = RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT;
    protected $privilege_TELECHARGER_SIEN = RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN;
    protected $privilege_VALIDER_TOUT = RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT;
    protected $privilege_VALIDER_SIEN = RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN;
    protected $privilege_DEVALIDER_TOUT = RapportPrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT;
    protected $privilege_DEVALIDER_SIEN = RapportPrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN;

    /**
     * @var Rapport[]
     */
    protected $rapportsTeleversesAnnuels = [];

    /**
     * @var Rapport[]
     */
    protected $rapportsTeleversesFintheses = [];

    /**
     * @var array [int => bool]
     */
    protected $canTeleverserRapportAnnuel;

    /**
     * @var array [int => bool]
     */
    protected $canTeleverserRapportFinthese;


    protected function fetchRapportsTeleverses()
    {
        parent::fetchRapportsTeleverses();

        $this->rapportsTeleversesAnnuels = array_filter($this->rapportsTeleverses, function(Rapport $rapport) {
            return $rapport->estFinal() === false;
        });
        $this->rapportsTeleversesFintheses = array_filter($this->rapportsTeleverses, function(Rapport $rapport) {
            return $rapport->estFinal() === true;
        });

        $this->canTeleverserRapportAnnuel = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canTeleverserRapportAnnuel[$anneeUniv->getPremiereAnnee()] =
                $this->canTeleverserRapportAnnuelForAnneeUniv($anneeUniv);
        }
        $this->canTeleverserRapportFinthese = [];
        foreach ($this->anneesUnivs as $anneeUniv) {
            $this->canTeleverserRapportFinthese[$anneeUniv->getPremiereAnnee()] =
                $this->canTeleverserRapportFintheseForAnneeUniv($anneeUniv);
        }
    }

    protected function isTeleversementPossible(): bool
    {
        return
            count(array_filter($this->canTeleverserRapportAnnuel)) > 0 ||
            count(array_filter($this->canTeleverserRapportFinthese)) > 0;
    }

    protected function getAnneesPrises(): array
    {
        return array_keys(
            array_intersect_key(
                array_filter($this->canTeleverserRapportAnnuel, function(bool $can) { return $can === false; }),
                array_filter($this->canTeleverserRapportFinthese, function(bool $can) { return $can === false; })
            )
        );
    }

    protected function canTeleverserRapport(Rapport $rapport): bool
    {
        if ($rapport->estFinal()) {
            return $this->canTeleverserRapportFintheseForAnneeUniv($rapport->getAnneeUniv());
        } else {
            return $this->canTeleverserRapportAnnuelForAnneeUniv($rapport->getAnneeUniv());
        }
    }

    protected function canTeleverserRapportAnnuel(): bool
    {
        // Peut être téléversé : 1 rapport annuel par année universitaire.

        foreach ($this->anneesUnivs as $anneeUniv) {
            $rapportsTeleverses = array_filter(
                $this->rapportsTeleversesAnnuels,
                $this->rapportService->getFilterRapportsByAnneeUniv($anneeUniv)
            );
            if (empty($rapportsTeleverses)) {
                return true;
            }
        }

        return false;
    }

    protected function canTeleverserRapportAnnuelForAnneeUniv(AnneeUniv $anneeUniv): bool
    {
        // Peut être téléversé : 1 rapport annuel.

        $rapportsTeleverses = array_filter(
            $this->rapportsTeleversesAnnuels,
            $this->rapportService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsTeleverses);
    }

    protected function canTeleverserRapportFinthese(): bool
    {
        // Dépôt d'1 rapport de fin de thèse maxi toutes années univ confondues.

        return count($this->rapportsTeleversesFintheses) === 0;
    }

    protected function canTeleverserRapportFintheseForAnneeUniv(AnneeUniv $anneeUniv): bool
    {
        // Dépôt d'un rapport de fin de thèse seulement sur la dernière année univ.

        if ($anneeUniv !== $this->getAnneeUnivMax()) {
            return false;
        }

        $rapportsTeleverses = array_filter(
            $this->rapportsTeleversesFintheses,
            $this->rapportService->getFilterRapportsByAnneeUniv($anneeUniv)
        );

        return empty($rapportsTeleverses);
    }

    protected function initForm()
    {
        parent::initForm();

        $estFinalValueOptions = [];
        if ($this->canTeleverserRapportAnnuel()) {
            $estFinalValueOptions['0'] = "Rapport d'activité annuel";
        }
        if ($this->canTeleverserRapportFinthese()) {
            $estFinalValueOptions['1'] = "Rapport d'activité de fin de thèse";
        }
        $this->form->setEstFinalValueOptions($estFinalValueOptions);
    }
}
