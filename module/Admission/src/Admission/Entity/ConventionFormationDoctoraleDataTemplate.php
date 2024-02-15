<?php

namespace Admission\Entity;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Filter\AdmissionFinancementFormatter;
use Admission\Filter\AdmissionInscriptionFormatter;
use Admission\Filter\AdmissionOperationsFormatter;

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

    public function getConventionCollaborationInformationstoHtml()
    {
        $admissionFinancementFormatter = new AdmissionFinancementFormatter();
        $inscription = $this->admission->getInscription()->first();
        $etablissementInscription = $inscription->getEtablissementInscription() ? $inscription->getEtablissementInscription()->getStructure() : null;
        return $admissionFinancementFormatter->htmlifyConventionCollaborationInformations($this->admission->getFinancement()->first(), $etablissementInscription);
    }

    public function getResponsablesURDirecteurtoHtml()
    {
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        return $admissionInscriptionFormatter->htmlifyResponsablesURDirecteur($this->getIndividuResponsablesUniteRechercheDirecteur());

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
        $operationsToPrint["header"] = ["Opération", "Valeur", "Acteur", "Date de l'opération"];
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