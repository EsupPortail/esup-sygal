<?php

namespace InscriptionAdministrative\Rule\Reception;

use Admission\Entity\Db\Admission;
use Application\Rule\RuleInterface;
use Doctorant\Entity\Db\Doctorant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;

interface ReceptionRuleInterface extends RuleInterface
{
    public function setInscriptionAdministrative(InscriptionAdministrative $inscriptionAdministrative): void;

    /**
     * Active ou non la mise à jour du doctorant lorsque c'est possible.
     * Mise à jour à partir des données "apprenant".
     */
    public function setDoctorantUpdateEnabled(bool $doctorantUpdateEnabled = true): static;

    /**
     * Point d'entrée.
     */
    public function execute(): void;

    public function canPerformAnyAction(): bool;

    public function canCreateThese(): bool;
    public function canCreateTheseAnneeUniv(): bool;
    public function canCreateDoctorant(): bool;
    public function canUpdateDoctorant(): bool;
    public function canUpdateCodeApprenantDoctorant(): bool;
    public function canCreateLinkInscriptionAdministrativeDoctorant(): bool;
    public function canLinkAdmissionToDoctorant(): bool;
    public function canLinkTheseAnneeUnivToInscriptionAdministrative(): bool;

    public function getFoundDoctorant(): ?Doctorant;
    public function getFoundAdmission(): ?Admission;
    public function getFoundThese(): ?These;
    public function getFoundTheseAnneeUniv(): ?TheseAnneeUniv;

    public function getMessagesForActions(): array;
    public function getMessagesForWarnings(): array;
}