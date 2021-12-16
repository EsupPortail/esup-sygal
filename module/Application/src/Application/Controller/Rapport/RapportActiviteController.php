<?php

namespace Application\Controller\Rapport;

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


    protected function loadRapportsTeleverses()
    {
        parent::loadRapportsTeleverses();

        $this->rapportsTeleversesAnnuels = array_filter($this->rapportsTeleverses, function(Rapport $rapport) {
            return $rapport->estFinal() === false;
        });
        $this->rapportsTeleversesFintheses = array_filter($this->rapportsTeleverses, function(Rapport $rapport) {
            return $rapport->estFinal() === true;
        });
    }

    protected function canTeleverserRapportAnnuel(): bool
    {
        // Peut être téléversé : 1 rapport annuel par année universitaire.

        $rapportsSurAnneeCourante = array_filter($this->rapportsTeleversesAnnuels, function(Rapport $rapport) {
            return $rapport->getAnneeUniv() === $this->anneeUnivCourante->getPremiereAnnee();
        });

        return count($rapportsSurAnneeCourante) === 0;
    }

    protected function canTeleverserRapportFinthese(): bool
    {
        // Peut être téléversé : 1 rapport de fin de thèse, toutes années univ confondues.

        return count($this->rapportsTeleversesFintheses) === 0;
    }

    protected function isTeleversementPossible(): bool
    {
        return
            $this->canTeleverserRapportAnnuel() ||
            $this->canTeleverserRapportFinthese();
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

    public function telechargerAction()
    {
        $rapport = $this->requestedRapport();

        // s'il s'agit d'un rapport d'activité validé, on ajoute une page de couverture
        if ($rapport->getRapportValidation() !== null) {
            $pdcData = $this->theseService->fetchInformationsPageDeCouverture($rapport->getThese());
            $outputFilePath = $this->rapportService->ajouterPdc($rapport, $pdcData);
            $this->fileService->downloadFile($outputFilePath);
            exit;
        }

        return parent::telechargerAction();
    }
}
