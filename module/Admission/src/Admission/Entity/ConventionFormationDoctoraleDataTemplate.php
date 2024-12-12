<?php

namespace Admission\Entity;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Entity\Db\Financement;
use Admission\Filter\AdmissionInscriptionFormatter;
use Admission\Filter\AdmissionOperationsFormatter;

/**
 * @deprecated
 */
class ConventionFormationDoctoraleDataTemplate
{
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
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
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
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
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
}