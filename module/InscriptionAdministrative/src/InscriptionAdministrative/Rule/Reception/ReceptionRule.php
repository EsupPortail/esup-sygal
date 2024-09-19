<?php

namespace InscriptionAdministrative\Rule\Reception;

use Admission\Entity\Db\Admission;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Rule\RuleInterface;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Entity\Db\Traits\InscriptionAdministrativeAwareTrait;
use InvalidArgumentException;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Traits\MessageAwareTrait;

class ReceptionRule implements RuleInterface
{
    use MessageAwareTrait;

    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use TheseServiceAwareTrait;
    use AdmissionServiceAwareTrait;

    use InscriptionAdministrativeAwareTrait;

    const ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE =
        "Anomalie ? Une admission existe pour le doctorant  mais elle n'est pas validée.";
    const ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION =
        "Anomalie ? IA concernant un doctorant connu (code apprenant) mais n'ayant ni thèse ni admission";

    /**
     * Active ou non la mise à jour du doctorant lorsque c'est possible.
     * Mise à jour à partir des données "apprenant".
     */
    protected bool $doctorantUpdateEnabled;

    private InscriptionAdministrativeApprenant $inscriptionAdministrativeApprenant;
    private ?InscriptionAdministrativeDoctorant $inscriptionAdministrativeDoctorant;

    private ?Admission $admission;
    private ?Doctorant $doctorant;

    private bool $canCreateThese;
    private bool $canCreateDoctorant;
    private bool $canApplyCodeApprenantToDoctorant;
    private bool $canCreateLinkInscriptionAdministrativeDoctorant;
    private bool $canLinkAdmissionToDoctorant;
    private bool $canUpdateDoctorant;

    private string $messageSeverityForAction = MessageAwareInterface::INFO;
    private string $messageSeverityForWarning = MessageAwareInterface::WARNING;


    public function setInscriptionAdministrative(InscriptionAdministrative $inscriptionAdministrative): void
    {
        $this->inscriptionAdministrative = $inscriptionAdministrative;
        $this->reset();
    }

    protected function reset(): void
    {
        $this->canCreateThese = false;
        $this->canCreateDoctorant = false;
        $this->canApplyCodeApprenantToDoctorant = false;
        $this->canCreateLinkInscriptionAdministrativeDoctorant = false;
        $this->canLinkAdmissionToDoctorant = false;
        $this->canUpdateDoctorant = false;
    }

    /**
     * Active ou non la mise à jour du doctroant lorsque c'est possible.
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

        // IA validée ou annulée ?
        if ($this->inscriptionAdministrative->estValidee()) {
            $this->processInscriptionValidee();
        } elseif ($this->inscriptionAdministrative->estAnnulee()) {
            $this->processInscriptionAnnulee();
        } else {
            throw new InvalidArgumentException("Statut inscription inattendu");
        }
    }



    /****************************************************************************************************************
     **                                               IA validée                                                   **
     ****************************************************************************************************************/

    private function processInscriptionValidee(): void
    {
        // IA déjà liée à un doctorant ?
        // (n'a pas de sens pour une IA en cours de réception mais pas très grave)
        /** @var InscriptionAdministrativeDoctorant $iad */
        $this->inscriptionAdministrativeDoctorant = $this->inscriptionAdministrative->getInscriptionAdministrativeDoctorant();
        if ($this->inscriptionAdministrativeDoctorant !== null) {
            $this->doctorant = $this->inscriptionAdministrativeDoctorant->getDoctorant();
            /*** Un doctorant existe avec ce code apprenant ***/
            // la suite est la même que lorsqu'un doctorant est trouvé avec le code apprenant
            $this->processInscriptionDejaLieeAUnDoctorant();
            return;
        }

        // Doctorant existant avec le même code apprenant ?
        $codeApprenant = $this->inscriptionAdministrativeApprenant->getCode();
        $this->doctorant = $this->doctorantService->getRepository()->findOneByCodeApprenant($codeApprenant);
        if ($this->doctorant !== null) {
            /*** Un doctorant existe avec ce code apprenant ***/
            $this->processDoctorantExistantAvecCodeApprenant();
        } else {
            /*** Aucun doctorant avec ce code apprenant ***/
            $this->processDoctorantInconnuAvecCodeApprenant();
        }
    }

    /**
     * Cas de figure où l'IA est déjà liée à un doctorant
     * (via {@see \InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant}).
     */
    private function processInscriptionDejaLieeAUnDoctorant(): void
    {
        // la suite est la même que lorsqu'un doctorant est trouvé avec le code apprenant
        $this->processDoctorantExistantAvecCodeApprenant();
    }

    /**
     * Cas de figure où un doctroant existe avec le code apprenant figurant dans l'IA.
     */
    private function processDoctorantExistantAvecCodeApprenant(): void
    {
        // Mise à jour (si mécanisme activé) du doctorant à partir des données Apprenant
        if ($this->doctorantUpdateEnabled) {
            $this->updateDoctorantFromApprenant();
        }

        // Thèse existante pour ce doctorant ?
        $theses = $this->theseService->getRepository()->findThesesByDoctorant($this->doctorant);
        if (empty($theses)) {
            /*** Aucune these trouvée pour ce doctorant ***/
            // Admission existante pour ce doctorant ?
            $this->admission = $this->admissionService->getRepository()->findOneByDoctorant($this->doctorant);

            if ($this->admission !== null) {
                if ($this->admission->getEtat()->getCode() === Admission::ETAT_VALIDE) {
                    /*** L'admission est validée ***/
                    // Création de la thèse à partir de l'Admission
                    $this->createTheseFromAdmission();
                } else {
                    /*** L'admission n'est pas encore validée : anomalie ? ***/
                    $this->addMessage(
                        self::ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE,
                        $this->messageSeverityForWarning
                    );
                }
            } else {
                /*** Aucune admission trouvée : anomalie ? ***/
                $this->addMessage(
                    self::ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION,
                    $this->messageSeverityForWarning
                );
            }
        }

        // Créer le lien IA-Doctorant si besoin
        $this->createLinkInscriptionAdministrativeDoctorant();
    }

    /**
     * Cas de figure où aucun doctorant n'existe avec le code apprenant figurant dans l'IA.
     */
    private function processDoctorantInconnuAvecCodeApprenant(): void
    {
        // Admission existante avec ce numéro candidat ?
        // (Remarque : Un numéro candidat est forcément présent, d'après le contrat d'interface Pégase.)
        $this->admission = $this->admissionService->getRepository()->findOneByNumeroCandidature($this->inscriptionAdministrative->getNoCandidat());

        if ($this->admission !== null) {
            /*** Admission trouvée avec ce numéro candidat ***/
            if ($this->admission->getEtat()->getCode() !== Admission::ETAT_VALIDE) {
                /*** L'admission n'est pas validée ***/
                // C'est anormal : rejet de l'IA !
                throw new InvalidArgumentException("Admission non encore validée !");
            }

            // Admission déjà liée à un doctorant ?
            if (($doctorant = $this->admission->getDoctorant()) !== null) {
                /*** Admission déjà liée à un doctorant ***/
                // Créer le lien IA-Doctorant si besoin
                $this->doctorant = $doctorant;
                $this->createLinkInscriptionAdministrativeDoctorant();
            } else {
                /*** Admission non liée à un doctorant ***/
                // Créer Doctorant
                $this->createDoctorantFromApprenant();
                // Créer These
                $this->createTheseFromAdmission();
                // Lier Admission au Doctorant
                $this->linkAdmissionToDoctorant();
            }
        } else {
            /*** Aucune Admission trouvée avec ce numéro candidat ***/
            // Doctorant existant avec cet INE ?
            $this->doctorant = $this->doctorantService->getRepository()->findOneByIne(
                $this->inscriptionAdministrativeApprenant->getIne()
            );

            if ($this->doctorant !== null) {
                /*** Doctorant trouvé avec cet INE ***/
                // (Remarque : Le doctorant peut avoir une thèse ou pas.)
                // Renseigner le code apprenant du doctorant
                $this->applyCodeApprenantToDoctorant();
                // Mise à jour (si mécanisme activé) du doctorant à partir des données Apprenant
                if ($this->doctorantUpdateEnabled) {
                    $this->updateDoctorantFromApprenant();
                }
                // Créer le lien IA-Doctorant
                $this->createLinkInscriptionAdministrativeDoctorant();
            } else {
                /*** Aucun Doctorant trouvé avec cet INE ***/
                // (Remarque : Pas possible de créer la Thèse sans Admission ! Elle devra être créée manuellement.)
                // Créer Doctorant
                $this->createDoctorantFromApprenant();
            }
        }

        // Créer le lien IA-Doctorant si besoin
        $this->createLinkInscriptionAdministrativeDoctorant();
    }

    private function createDoctorantFromApprenant(): void
    {
        $message = "Création possible d'un doctorant à partir des données sur l'apprenant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateDoctorant = true;
    }

    private function updateDoctorantFromApprenant(): void
    {
        $message = "Mise à jour possible du doctorant à partir des données sur l'apprenant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $diffIndividu = $this->individuService->diffIndividuFromApprenant(
            $this->doctorant->getIndividu(),
            $this->inscriptionAdministrativeApprenant);
        $diffDoctorant = $this->doctorantService->diffDoctorantFromApprenant(
            $this->doctorant,
            $this->inscriptionAdministrativeApprenant);

        $this->canUpdateDoctorant = !empty($diffIndividu) || !empty($diffDoctorant);
    }

    private function createTheseFromAdmission(): void
    {
        $message = "Création possible d'une nouvelle thèse pour le doctorant à partir du dossier d'admission.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateThese = true;
    }

    private function applyCodeApprenantToDoctorant(): void
    {
        $codeApprenant = $this->inscriptionAdministrativeApprenant->getCode();

        $message = sprintf(
            "Enregistrement possible du code apprenant %s sur le doctorant.",
            $codeApprenant,
        );
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canApplyCodeApprenantToDoctorant = true;
    }

    private function createLinkInscriptionAdministrativeDoctorant(): void
    {
        // si le lien existe déjà, noop
        if ($this->inscriptionAdministrativeDoctorant !== null) {
            return;
        }

        $message = "Création possible du lien entre cette inscription administrative et le doctorant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateLinkInscriptionAdministrativeDoctorant = true;
    }

    private function linkAdmissionToDoctorant(): void
    {
        $message = "Création possible du lien entre l'admission et le doctorant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canLinkAdmissionToDoctorant = true;
    }



    /****************************************************************************************************************
     **                                                  IA annulée                                                **
     ****************************************************************************************************************/

    private function processInscriptionAnnulee(): void
    {
        // ??
    }




    /************************************************ Communs ****************************************************/

    public function canCreateThese(): bool
    {
        return $this->canCreateThese;
    }

    public function canCreateDoctorant(): bool
    {
        return $this->canCreateDoctorant;
    }

    public function canUpdateDoctorant(): bool
    {
        return $this->canUpdateDoctorant;
    }

    public function canUpdateCodeApprenantDoctorantFromApprenant(): bool
    {
        return $this->canApplyCodeApprenantToDoctorant;
    }

    public function canCreateLinkInscriptionAdministrativeDoctorant(): bool
    {
        return $this->canCreateLinkInscriptionAdministrativeDoctorant;
    }

    public function canLinkAdmissionToDoctorant(): bool
    {
        return $this->canLinkAdmissionToDoctorant;
    }

    public function canPerformAnyAction(): bool
    {
        return $this->canCreateThese
            || $this->canCreateDoctorant
            || $this->canUpdateDoctorant
            || $this->canApplyCodeApprenantToDoctorant
            || $this->canCreateLinkInscriptionAdministrativeDoctorant
            || $this->canLinkAdmissionToDoctorant;
    }

    public function getDoctorant(): ?Doctorant
    {
        return $this->doctorant;
    }

    public function getAdmission(): ?Admission
    {
        return $this->admission;
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