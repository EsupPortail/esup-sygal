<?php

namespace Admission\Entity;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Filter\AdmissionEtudiantFormatter;
use Admission\Filter\AdmissionOperationsFormatter;

/**
 * @deprecated
 */
class AdmissionRecapitulatifDataTemplate
{
    private Admission $admission;
    private array $operations = [];

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
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDiplomeIntituleInformationstoHtmlArray()
    {
        $etudiantFormatter = new AdmissionEtudiantFormatter();
        return $etudiantFormatter->htmlifyDiplomeIntituleInformations($this->admission->getEtudiant()->first());
    }
}