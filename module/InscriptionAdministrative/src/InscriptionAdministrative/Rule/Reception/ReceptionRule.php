<?php

namespace InscriptionAdministrative\Rule\Reception;

use Admission\Entity\Db\Admission;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Entity\AnneeUniv;
use Application\MessageAwareTrait;
use Application\Rule\RuleInterface;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Entity\Db\Traits\InscriptionAdministrativeAwareTrait;
use InvalidArgumentException;
use These\Entity\Db\These;
use These\Entity\Db\TheseAnneeUniv;
use These\Service\These\TheseServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareInterface;

class ReceptionRule implements RuleInterface
{
    use MessageAwareTrait;

    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use AdmissionServiceAwareTrait;

    use InscriptionAdministrativeAwareTrait;

    const ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE =
        "Anomalie ? Une admission existe pour le doctorant  mais elle n'est pas validée.";
    const ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION =
        "Anomalie ? IA concernant un doctorant connu (code apprenant) mais n'ayant ni thèse ni admission";
    const PB_LE_DOCTORANT_POSSEDE_PLUSIEURS_THESES_EN_COURS =
        "Problème ! Le doctorant possède plusieurs thèses en cours.";

    /**
     * Active ou non la mise à jour du doctorant lorsque c'est possible.
     * Mise à jour à partir des données "apprenant".
     */
    protected bool $doctorantUpdateEnabled;

    private InscriptionAdministrativeApprenant $inscriptionAdministrativeApprenant;
    private ?InscriptionAdministrativeDoctorant $inscriptionAdministrativeDoctorant;

    private ?Admission $admission;
    private ?Doctorant $doctorant;
    private ?These $these;
    private ?TheseAnneeUniv $theseAnneeUniv;

    private bool $canCreateThese;
    private bool $canCreateTheseAnneeUniv;
    private bool $canCreateDoctorant;
    private bool $canApplyCodeApprenantToDoctorant;
    private bool $canCreateLinkInscriptionAdministrativeDoctorant;
    private bool $canLinkAdmissionToDoctorant;
    private bool $canLinkTheseAnneeUnivToInscriptionAdministrative;
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
        $this->canCreateTheseAnneeUniv = false;
        $this->canCreateDoctorant = false;
        $this->canApplyCodeApprenantToDoctorant = false;
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
        if (count($theses) === 0) {
            /*** Aucune these trouvée pour ce doctorant ***/
            // Admission existante pour ce doctorant ?
            $this->admission = $this->admissionService->getRepository()->findOneByDoctorant($this->doctorant);

            if ($this->admission !== null) {
                if ($this->admission->getEtat()->getCode() === Admission::ETAT_VALIDE) {
                    /*** L'admission est validée ***/
                    // Création de la thèse à partir de l'Admission
                    $this->createTheseFromAdmission();
                    // Créer une TheseAnneeUniv
                    $this->createTheseAnneeUniv();
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
        } else {
            /*** Thèses en cours trouvées ***/
            if (count($theses) > 1) {
                /*** Plusieurs thèses en cours trouvées pour un même doctorant : pb pour gérer l'année universitaire ! ***/
                $this->addMessage(
                    self::PB_LE_DOCTORANT_POSSEDE_PLUSIEURS_THESES_EN_COURS . " L'année universitaire ne sera pas créée/màj.",
                    $this->messageSeverityForWarning
                );
            } else {
                /*** Une seule thèse : cool ***/
                $this->these = $theses[0];
                $annee = $this->inscriptionAdministrative->getPeriodeAnneeUniversitaire();
                $theseAnneeUnivExistante =
                    $this->theseAnneeUnivService->getRepository()->findOneTheseAnneeUnivForTheseAndAnnee($this->these, $annee);
                if ($theseAnneeUnivExistante !== null) {
                    /*** Une année universitaire existe déjà pour cette thèse et cette année ***/
                    if ($theseAnneeUnivExistante->getInscriptionAdministrative() !== $this->inscriptionAdministrative) {
                        /*** L'IA liée est null ou différente ***/
                        /*** NB : on décide de ne pas distinguer les cas 'IA liée différente' et 'Aucune IA liée'. ***/
                        // Créer le lien TheseAnneeUniv-IA
                        $this->linkTheseAnneeUnivToInscriptionAdministrative($theseAnneeUnivExistante);
                    }
                } else {
                    /*** Pas d'année universitaire trouvée ***/
                    // Créer une TheseAnneeUniv
                    $this->createTheseAnneeUniv();
                }
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
                // Créer une TheseAnneeUniv
                $this->createTheseAnneeUniv();
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
        $diffIndividu = $this->individuService->diffIndividuFromApprenant(
            $this->doctorant->getIndividu(),
            $this->inscriptionAdministrativeApprenant);
        $diffDoctorant = $this->doctorantService->diffDoctorantFromApprenant(
            $this->doctorant,
            $this->inscriptionAdministrativeApprenant);

        if (!empty($diffIndividu) || !empty($diffDoctorant)) {
            $message = "Mise à jour possible du doctorant à partir des données sur l'apprenant : ";
            if (!empty($diffIndividu)) {
                $message .= implode(', ', array_map(
                    fn($arr) => $arr[0].'=>'.$arr[1],
                    array_values(array_merge($diffIndividu, $diffDoctorant))
                ));
            }
            $this->addMessage($message, $this->messageSeverityForAction);

            $this->canUpdateDoctorant = true;
        }
    }

    private function createTheseFromAdmission(): void
    {
        $message = "Création possible d'une nouvelle thèse pour le doctorant à partir du dossier d'admission.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateThese = true;
    }

    private function createTheseAnneeUniv(): void
    {
        $message = sprintf(
            "Création possible de l'année universitaire %s pour la thèse, à partir de la période de l'inscription administrative.",
            AnneeUniv::fromPremiereAnnee($this->inscriptionAdministrative->getPeriodeAnneeUniversitaire())
        );
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateTheseAnneeUniv = true;
    }

    private function linkTheseAnneeUnivToInscriptionAdministrative(TheseAnneeUniv $theseAnneeUniv): void
    {
        $message = sprintf(
            "Création possible du lien entre l'année universitaire %s de la thèse %s et l'inscription administrative.",
            $theseAnneeUniv,
            $theseAnneeUniv->getThese()->getId()
        );
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->theseAnneeUniv = $theseAnneeUniv;
        $this->canLinkTheseAnneeUnivToInscriptionAdministrative = true;
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

    public function canLinkTheseAnneeUnivToInscriptionAdministrative(): bool
    {
        return $this->canLinkTheseAnneeUnivToInscriptionAdministrative;
    }

    public function canPerformAnyAction(): bool
    {
        return $this->canCreateThese
            || $this->canCreateTheseAnneeUniv
            || $this->canCreateDoctorant
            || $this->canUpdateDoctorant
            || $this->canApplyCodeApprenantToDoctorant
            || $this->canCreateLinkInscriptionAdministrativeDoctorant
            || $this->canLinkAdmissionToDoctorant
            || $this->canLinkTheseAnneeUnivToInscriptionAdministrative;
    }

    public function getDoctorant(): ?Doctorant
    {
        return $this->doctorant;
    }

    public function getAdmission(): ?Admission
    {
        return $this->admission;
    }

    public function getThese(): ?These
    {
        return $this->these;
    }

    public function getTheseAnneeUniv(): ?TheseAnneeUniv
    {
        return $this->theseAnneeUniv;
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