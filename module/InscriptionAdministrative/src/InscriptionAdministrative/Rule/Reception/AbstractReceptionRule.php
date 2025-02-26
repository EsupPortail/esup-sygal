<?php

namespace InscriptionAdministrative\Rule\Reception;

use Admission\Entity\Db\Admission;
use Application\MessageAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Entity\Db\Traits\InscriptionAdministrativeAwareTrait;
use InvalidArgumentException;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;
use UnicaenApp\Traits\MessageAwareInterface;

abstract class AbstractReceptionRule implements ReceptionRuleInterface
{
    use MessageAwareTrait;

    use InscriptionAdministrativeAwareTrait;

    protected InscriptionAdministrativeApprenant $inscriptionAdministrativeApprenant;
    protected ?InscriptionAdministrativeDoctorant $inscriptionAdministrativeDoctorant;

    protected ?Admission $foundAdmission;
    protected ?Doctorant $foundDoctorant;
    protected ?These $foundThese;
    protected ?TheseAnneeUniv $foundTheseAnneeUniv;

    protected bool $canCreateThese;
    protected bool $canCreateTheseAnneeUniv;
    protected bool $canCreateDoctorant;
    protected bool $canUpdateCodeApprenantDoctorant;
    protected bool $canCreateLinkInscriptionAdministrativeDoctorant;
    protected bool $canLinkAdmissionToDoctorant;
    protected bool $canLinkTheseAnneeUnivToInscriptionAdministrative;
    protected bool $canUpdateDoctorant;

    /**
     * Active ou non la mise à jour du doctorant lorsque c'est possible.
     * Mise à jour à partir des données "apprenant".
     */
    protected bool $doctorantUpdateEnabled;

    protected string $messageSeverityForAction = MessageAwareInterface::INFO;
    protected string $messageSeverityForWarning = MessageAwareInterface::WARNING;

    public function setInscriptionAdministrative(InscriptionAdministrative $inscriptionAdministrative): void
    {
        $this->inscriptionAdministrative = $inscriptionAdministrative;
        $this->reset();
    }

    protected function reset(): void
    {
        $this->canCreateThese = false;
        $this->canCreateTheseAnneeUniv = false;
        $this->canCreateDoctorant = false;
        $this->canUpdateCodeApprenantDoctorant = false;
        $this->canCreateLinkInscriptionAdministrativeDoctorant = false;
        $this->canLinkAdmissionToDoctorant = false;
        $this->canLinkTheseAnneeUnivToInscriptionAdministrative = false;
        $this->canUpdateDoctorant = false;
    }

    /**
     * Active ou non la mise à jour du doctorant lorsque c'est possible.
     * Mise à jour à partir des données "apprenant".
     */
    public function setDoctorantUpdateEnabled(bool $doctorantUpdateEnabled = true): static
    {
        $this->doctorantUpdateEnabled = $doctorantUpdateEnabled;
        return $this;
    }

    /**
     * Point d'entrée.
     *
     * @see doc/integration_flux_inscrip_admin.drawio
     */
    public function execute(): void
    {
        $this->reset();

        /** @var InscriptionAdministrativeApprenant $iaApprenant */
        $iaApprenant = $this->inscriptionAdministrative->getInscriptionAdministrativeApprenant();
        if ($iaApprenant === null) {
            throw new InvalidArgumentException("Anomalie : l'IA n'est liée à aucun apprenant");
        }

        $this->inscriptionAdministrativeApprenant = $iaApprenant;
    }


    public function canPerformAnyAction(): bool
    {
        return $this->canCreateDoctorant
            || $this->canUpdateDoctorant
            || $this->canCreateThese
            || $this->canCreateTheseAnneeUniv
            || $this->canUpdateCodeApprenantDoctorant
            || $this->canLinkAdmissionToDoctorant
            || $this->canCreateLinkInscriptionAdministrativeDoctorant
            || $this->canLinkTheseAnneeUnivToInscriptionAdministrative;
    }

    public function canCreateThese(): bool
    {
        return $this->canCreateThese;
    }

    public function canCreateTheseAnneeUniv(): bool
    {
        return $this->canCreateTheseAnneeUniv;
    }

    public function canCreateDoctorant(): bool
    {
        return $this->canCreateDoctorant;
    }

    public function canUpdateDoctorant(): bool
    {
        return $this->canUpdateDoctorant;
    }

    public function canUpdateCodeApprenantDoctorant(): bool
    {
        return $this->canUpdateCodeApprenantDoctorant;
    }

    public function canCreateLinkInscriptionAdministrativeDoctorant(): bool
    {
        return $this->canCreateLinkInscriptionAdministrativeDoctorant;
    }

    public function canLinkAdmissionToDoctorant(): bool
    {
        return $this->canLinkAdmissionToDoctorant;
    }

    public function canLinkTheseAnneeUnivToInscriptionAdministrative(): bool
    {
        return $this->canLinkTheseAnneeUnivToInscriptionAdministrative;
    }


    public function getFoundDoctorant(): ?Doctorant
    {
        return $this->foundDoctorant;
    }

    public function getFoundAdmission(): ?Admission
    {
        return $this->foundAdmission;
    }

    public function getFoundThese(): ?These
    {
        return $this->foundThese;
    }

    public function getFoundTheseAnneeUniv(): ?TheseAnneeUniv
    {
        return $this->foundTheseAnneeUniv;
    }


    public function getMessagesForActions(): array
    {
        return $this->getMessages($this->messageSeverityForAction);
    }

    public function getMessagesForWarnings(): array
    {
        return $this->getMessages($this->messageSeverityForWarning);
    }
}