<?php

namespace Admission\Renderer;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\ConventionFormationDoctorale;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
use Admission\Filter\AdmissionInscriptionFormatter;
use Admission\Filter\AdmissionOperationsFormatter;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class AdmissionConventionFormationDoctoraleTemplateVariable extends AbstractTemplateVariable
{
    private ConventionFormationDoctorale $conventionFormationDoctorale;

    public function setConventionFormationDoctorale(ConventionFormationDoctorale $conventionFormationDoctorale): void
    {
        $this->conventionFormationDoctorale = $conventionFormationDoctorale;
    }

    public function getCalendrierProjetRecherche(): ?string
    {
        return $this->conventionFormationDoctorale->getCalendrierProjetRecherche();
    }

    public function getModalitesEncadrSuiviAvancmtRech(): ?string
    {
        return $this->conventionFormationDoctorale->getModalitesEncadrSuiviAvancmtRech();
    }

    public function getConditionsRealisationProjRech(): ?string
    {
        return $this->conventionFormationDoctorale->getConditionsRealisationProjRech();
    }

    public function getModalitesIntegrationUr(): ?string
    {
        return $this->conventionFormationDoctorale->getModalitesIntegrationUr();
    }

    public function getPartenariatsProjThese(): ?string
    {
        return $this->conventionFormationDoctorale->getPartenariatsProjThese();
    }

    public function getMotivationDemandeConfidentialite(): ?string
    {
        return $this->conventionFormationDoctorale->getMotivationDemandeConfidentialite();
    }

    public function getProjetProDoctorant(): ?string
    {
        return $this->conventionFormationDoctorale->getProjetProDoctorant();
    }


    ////////////// repris de feu ConventionFormationDoctoraleDataTemplate ///////////////////

    private Admission $admission;
    private array $operations = [];
    private array $individuResponsablesUniteRechercheDirecteur = [];

    public function getIndividuResponsablesUniteRechercheDirecteur(): array
    {
        return $this->individuResponsablesUniteRechercheDirecteur;
    }

    public function setIndividuResponsablesUniteRechercheDirecteur(array $individuResponsablesUniteRechercheDirecteur): void
    {
        $this->individuResponsablesUniteRechercheDirecteur = $individuResponsablesUniteRechercheDirecteur;
    }

    public function getIndividuResponsablesUniteRechercheCoDirecteur(): array
    {
        return $this->individuResponsablesUniteRechercheCoDirecteur;
    }

    public function setIndividuResponsablesUniteRechercheCoDirecteur(array $individuResponsablesUniteRechercheCoDirecteur): void
    {
        $this->individuResponsablesUniteRechercheCoDirecteur = $individuResponsablesUniteRechercheCoDirecteur;
    }
    private array $individuResponsablesUniteRechercheCoDirecteur = [];

    public function getOperations(): array
    {
        return $this->operations;
    }
    public function setOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    public function getAdmission(): Admission
    {
        return $this->admission;
    }

    public function setAdmission(Admission $admission): void
    {
        $this->admission = $admission;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getCoDirectionInformationstoHtml()
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        return $admissionInscriptionFormatter->htmlifyDiplomeIntituleInformations($this->admission->getInscription()->first());
    }

    public function getCoTutelleInformationstoHtml()
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        return $admissionInscriptionFormatter->htmlifyCoTutelleInformations($this->admission->getInscription()->first());
    }

    public function getCoTutelleCoDirectionInformationstoHtml()
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        return $admissionInscriptionFormatter->htmlifyCoTutelleCoDirectionInformations($this->admission->getInscription()->first(), $this->getIndividuResponsablesUniteRechercheCoDirecteur());
    }

    public function getConventionCollaborationInformationstoHtml(): ?string
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        $inscription = $this->admission->getInscription()->first() ? $this->admission->getInscription()->first() : null;
        /** @var Financement $financement */
        $financement = $this->admission->getFinancement()->first() ? $this->admission->getFinancement()->first() : null;
        $estSalarie = $financement && $financement->getEstSalarie() ? $financement->getEstSalarie() : false;
        $etablissementPartenaire = $financement && $financement->getEtablissementPartenaire() ? $financement->getEtablissementPartenaire() : false;
        return $inscription && $etablissementPartenaire ? $admissionInscriptionFormatter->htmlifyConventionCollaborationInformations($inscription, $estSalarie, $etablissementPartenaire) : "";
    }

    public function getConfidentialiteInformationstoHtml(): ?string
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        $inscription = $this->admission->getInscription()->first() ? $this->admission->getInscription()->first() : null;
        $conventionFormationDoctorale = $this->admission->getConventionFormationDoctorale()->first() ? $this->admission->getConventionFormationDoctorale()->first() : null;
        return $inscription ? $admissionInscriptionFormatter->htmlifyConfidentialiteInformations($inscription, $conventionFormationDoctorale) : "";
    }

    public function getResponsablesURDirecteurtoHtml()
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        $inscription = $this->admission->getInscription()->first() ? $this->admission->getInscription()->first() : null;
        return $admissionInscriptionFormatter->htmlifyResponsablesURDirecteur($inscription, $this->getIndividuResponsablesUniteRechercheDirecteur());
    }

    public function getResponsablesURCoDirecteurtoHtml()
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        return $admissionInscriptionFormatter->htmlifyResponsablesURCoDirecteur($this->getIndividuResponsablesUniteRechercheCoDirecteur());
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getOperationstoHtmlArray()
    {
        $operations = $this->getOperations();
        $operationsFormatter = new AdmissionOperationsFormatter();
        $operationsToPrint["header"] = ["Opération", "", "Acteur", "Date de l'opération"];
        foreach($operations as $operation){
            if ($operation instanceof AdmissionValidation) {
                $libelleOperation = $operation->getTypeValidation()->getLibelle();
                $valeur = "/";
            }elseif ($operation instanceof AdmissionAvis) {
                $libelleOperation = $operation->getTypeToString();
                /** @var AdmissionAvis $operation */
                $valeur = $operation->getId() !== null ? $operation->getAvis()->getAvisValeur()->getValeur() : null;
            }
            $operationsToPrint["operations"][] = [
                "libelle" => $libelleOperation,
                "valeur" => $valeur,
                "createur" => $operation->getId() !== null ? $operation->getHistoCreateur() : "/",
                "dateCreation" => $operation->getId() !== null ? $operation->getHistoCreation()->format(\Application\Constants::DATETIME_FORMAT) : "/"
            ];
        }
        return $operationsFormatter->htmlifyOperations($operationsToPrint);
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getSignatairestoHtmlArray()
    {
        /** @var Inscription $inscription */
        $inscription = $this->admission->getInscription()->first() ? $this->admission->getInscription()->first() : null;

        $signatairesToPrint = [];
        $signatairesToPrint[] = ["Le doctorant (". $this->admission->getIndividu().")", ""];
        $signatairesToPrint[] = ["La direction de thèse (". $inscription->getDirecteur().")", $inscription->getCoDirection() ? "La co-direction de thèse (". $inscription->getCoDirecteur().")" : ""];

        if ($inscription->getCoEncadrement()) {
            $signatairesToPrint[] = [
                "Le co-encadrement de thèse si validé en Cac restreint de l’établissement d’inscription",
                "Le second co-encadrement de thèse si validé en Cac restreint de l’établissement d’inscription"
            ];
        }

        $signatairesToPrint[] = [
            "La direction de l’unité de recherche de la direction de thèse (". $inscription->getUniteRecherche()?->getStructure()->getSigle().")",
            ($inscription->getCoDirection() && $inscription->getUniteRecherche() && $inscription->getUniteRecherche() !== $inscription->getUniteRechercheCoDirecteur())
                ? "La direction de l’unité de recherche de la co-direction de thèse (". $inscription->getUniteRechercheCoDirecteur()?->getStructure()->getSigle().")"
                : ""
        ];

        $signatairesToPrint[] = [
            "La direction de l’école doctorale de la direction de thèse (". $inscription->getEcoleDoctorale()?->getStructure()->getSigle().")",
            $inscription->getCoDirection() ? "La direction de l’école doctorale de la co-direction de thèse (si différent de l'établissement d'inscription)" : ""
        ];

        $signatairesToPrint[] = [
            "La présidence ou direction de l’établissement d’inscription en doctorat (". $inscription->getEtablissementInscription()?->getStructure()->getSigle().")",
            ($inscription->getEtablissementInscription() !== $inscription->getEtablissementLaboratoireRecherche())
                ? "La présidence ou direction de l’établissement employeur du doctorant (si différent de l'établissement d'inscription)"
                : ""
        ];

        $signatairesToPrint[] = ["La présidence de Normandie Université", ""];

        $str = "<table id='signataires-convention'>";

        foreach ($signatairesToPrint as [$signataire, $coSignataire]) {
            $str .= "<tr>";
            if ($coSignataire) {
                $str .= "<td><b>Date : ………/………/……… <br>" . $signataire . "</b><br><i>(nom, prénom, signature)</i></td>";
                $str .= "<td><b>Date : ………/………/……… <br>" . $coSignataire . "</b><br><i>(nom, prénom, signature)</i></td>";
            } else {
                $str .= "<td colspan='2'><b>Date : ………/………/……… <br>" . $signataire . "</b><br><i>(nom, prénom, signature)</i></td>";
            }
            $str .= "</tr>";
        }

        $str .= "</table>";

        return $str;
    }
}