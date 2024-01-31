<?php

namespace Admission\Entity;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Entity\Db\AdmissionValidation;
use Admission\Filter\AdmissionInscriptionFormatter;
use Admission\Filter\AdmissionOperationsFormatter;

class ConventionFormationDoctoraleDataTemplate
{
    private Admission $admission;

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
        $admissionInscriptionFormatter = new AdmissionInscriptionFormatter();
        return "Je sais pas";
    }

}