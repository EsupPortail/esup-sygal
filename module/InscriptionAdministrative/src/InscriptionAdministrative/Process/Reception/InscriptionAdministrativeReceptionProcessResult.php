<?php

namespace InscriptionAdministrative\Process\Reception;

use Admission\Entity\Db\Admission;
use Doctorant\Entity\Db\Doctorant;
use InscriptionAdministrative\Process\ProcessResult;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;

class InscriptionAdministrativeReceptionProcessResult extends ProcessResult
{
    private ?Admission $foundAdmissionValidee = null;
    private ?Doctorant $doctorant = null;
    private ?These $createdThese = null;
    private ?TheseAnneeUniv $createdTheseAnneeUniv = null;
    private bool $isTheseCreated = false;
    private bool $isTheseAnneeUnivCreated = false;
    private bool $isDoctorantCreated = false;
    private bool $isDoctorantUpdated = false;

    public function isTheseCreated(): bool
    {
        return $this->isTheseCreated;
    }

    public function setIsTheseCreated(bool $isTheseCreated): self
    {
        $this->isTheseCreated = $isTheseCreated;
        return $this;
    }

    public function isTheseAnneeUnivCreated(): bool
    {
        return $this->isTheseAnneeUnivCreated;
    }

    public function setIsTheseAnneeUnivCreated(bool $isTheseAnneeUnivCreated): self
    {
        $this->isTheseAnneeUnivCreated = $isTheseAnneeUnivCreated;
        return $this;
    }

    public function setCreatedThese(?These $createdThese): self
    {
        $this->createdThese = $createdThese;
        return $this;
    }

    public function getCreatedThese(): ?These
    {
        return $this->createdThese;
    }

    public function setCreatedTheseAnneeUniv(?TheseAnneeUniv $createdTheseAnneeUniv): self
    {
        $this->createdTheseAnneeUniv = $createdTheseAnneeUniv;
        return $this;
    }

    public function getCreatedTheseAnneeUniv(): ?TheseAnneeUniv
    {
        return $this->createdTheseAnneeUniv;
    }

    public function isDoctorantCreated(): bool
    {
        return $this->isDoctorantCreated;
    }

    public function setIsDoctorantCreated(bool $isDoctorantCreated): self
    {
        $this->isDoctorantCreated = $isDoctorantCreated;
        return $this;
    }

    public function setIsDoctorantUpdated(bool $isDoctorantUpdated): self
    {
        $this->isDoctorantUpdated = $isDoctorantUpdated;
        return $this;
    }

    public function isDoctorantUpdated(): bool
    {
        return $this->isDoctorantUpdated;
    }

    /**
     * Spécifie le doctorant créé ou mis à jour.
     */
    public function setDoctorant(?Doctorant $doctorant): self
    {
        $this->doctorant = $doctorant;
        return $this;
    }

    /**
     * Retourne le doctorant créé ou mis à jour.
     */
    public function getDoctorant(): ?Doctorant
    {
        return $this->doctorant;
    }

    public function getFoundAdmissionValidee(): ?Admission
    {
        return $this->foundAdmissionValidee;
    }

    public function setFoundAdmissionValidee(?Admission $foundAdmissionValidee): self
    {
        $this->foundAdmissionValidee = $foundAdmissionValidee;
        return $this;
    }

}