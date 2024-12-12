<?php

namespace Admission\Renderer;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Filter\AdmissionEtudiantFormatter;
use Admission\Filter\AdmissionOperationsFormatter;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class AdmissionTemplateVariable extends AbstractTemplateVariable
{
    private Admission $admission;
    private array $operations = [];
    private ?string $operationAttenduNotificationAnomalies = null;
    private ?string $admissionAvisNotificationAnomalies = null;

    public function setOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    public function setAdmission(Admission $admission): void
    {
        $this->admission = $admission;
    }

    public function setOperationAttenduNotificationAnomalies(?string $anomalies): void
    {
        $this->operationAttenduNotificationAnomalies = $anomalies;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getOperationAttenduNotificationAnomalies()
    {
        return $this->operationAttenduNotificationAnomalies;
    }

    public function setAdmissionAvisNotificationAnomalies(?string $admissionAvisNotificationAnomalies = null): void
    {
        $this->admissionAvisNotificationAnomalies = $admissionAvisNotificationAnomalies;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAdmissionAvisNotificationAnomalies(): ?string
    {
        return $this->admissionAvisNotificationAnomalies;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getOperationstoHtmlArray()
    {
        $operations = $this->operations;
        $operationsFormatter = new AdmissionOperationsFormatter();
        $operationsToPrint["header"] = ["Opération", "", "Acteur", "Date de l'opération"];
        foreach($operations as $operation){
            if ($operation instanceof AdmissionValidation) {
                $libelleOperation = $operation->getTypeValidation()->getLibelle();
                $valeur = "/";
            }elseif ($operation instanceof AdmissionAvis) {
                if($operation->getAvis()->getAvisType()->getCode() === AdmissionAvis::AVIS_TYPE__CODE__AVIS_ADMISSION_PRESIDENCE) continue;
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
        $string = "<pagebreak />
                    <h2>Validations et Avis accordés au dossier d'admission</h2>";
        $string .= $operationsFormatter->htmlifyOperations($operationsToPrint);
        return $string;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDiplomeIntituleInformationstoHtmlArray()
    {
        $etudiantFormatter = new AdmissionEtudiantFormatter();
        return $etudiantFormatter->htmlifyDiplomeIntituleInformations($this->admission->getEtudiant()->first());
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateToString()
    {
        return $this->admission->getDateToString();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function __toString()
    {
        return $this->admission->__toString();
    }
}