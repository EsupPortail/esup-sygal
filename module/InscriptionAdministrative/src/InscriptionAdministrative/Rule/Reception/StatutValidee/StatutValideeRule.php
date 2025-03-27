<?php

namespace InscriptionAdministrative\Rule\Reception\StatutValidee;

use Admission\Entity\Db\Admission;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Entity\AnneeUniv;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeDoctorant;
use InscriptionAdministrative\Rule\Reception\AbstractReceptionRule;
use InvalidArgumentException;
use These\Entity\Db\TheseAnneeUniv;
use These\Service\These\TheseServiceAwareTrait;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceAwareTrait;

class StatutValideeRule extends AbstractReceptionRule
{
    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseAnneeUnivServiceAwareTrait;
    use AdmissionServiceAwareTrait;

    const ANOMALIE_UNE_ADMISSION_EXISTE_POUR_LE_DOCTORANT_MAIS_ELLE_N_EST_PAS_VALIDEE =
        "Anomalie ? Une admission existe pour le doctorant  mais elle n'est pas validée.";
    const ANOMALIE_IA_CONCERNANT_UN_DOCTORANT_CONNU_CODE_APPRENANT_MAIS_N_AYANT_NI_THESE_NI_ADMISSION =
        "Anomalie ? IA concernant un doctorant connu (code apprenant) mais n'ayant ni thèse ni admission.";
    const PB_LE_DOCTORANT_POSSEDE_PLUSIEURS_THESES_EN_COURS =
        "Problème ! Le doctorant possède plusieurs thèses en cours.";

    /**
     * Point d'entrée.
     *
     * @see doc/integration_flux_inscrip_admin.drawio
     */
    public function execute(): void
    {
        parent::execute();

        if (!$this->inscriptionAdministrative->estValidee()) {
            throw new InvalidArgumentException("Statut inscription inattendu");
        }

        $this->process();
    }

    private function process(): void
    {
        // IA déjà liée à un doctorant ?
        // (n'a pas de sens pour une IA en cours de réception mais pas très grave, on généralise)
        /** @var InscriptionAdministrativeDoctorant $iad */
        $this->inscriptionAdministrativeDoctorant = $this->inscriptionAdministrative->getInscriptionAdministrativeDoctorant();
        if ($this->inscriptionAdministrativeDoctorant !== null) {
            $this->foundDoctorant = $this->inscriptionAdministrativeDoctorant->getDoctorant();
            /*** Un doctorant existe avec ce code apprenant ***/
            // la suite est la même que lorsqu'un doctorant est trouvé avec le code apprenant
            $this->processInscriptionDejaLieeAUnDoctorant();
            return;
        }

        // Doctorant existant avec le même code apprenant ?
        $codeApprenant = $this->inscriptionAdministrativeApprenant->getCode();
        $this->foundDoctorant = $this->doctorantService->getRepository()->findOneByCodeApprenant($codeApprenant);
        if ($this->foundDoctorant !== null) {
            /*** Un doctorant existe avec ce code apprenant ***/
            $this->processDoctorantExistant();
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
        $this->processDoctorantExistant();
    }

    /**
     * Cas de figure où un doctroant existe avec le code apprenant figurant dans l'IA.
     */
    private function processDoctorantExistant(): void
    {
        // Mise à jour du doctorant à partir des données Apprenant
        $this->setCanUpdateDoctorant();

        // Thèse existante pour ce doctorant ?
        $theses = $this->theseService->getRepository()->findThesesByDoctorant($this->foundDoctorant);
        if (count($theses) === 0) {
            /*** Aucune these trouvée pour ce doctorant ***/
            $this->foundThese = null;

            // Admission existante pour ce doctorant ?
            $this->foundAdmission = $this->admissionService->getRepository()->findOneByDoctorant($this->foundDoctorant);

            if ($this->foundAdmission !== null) {
                if ($this->foundAdmission->getEtat()->getCode() === Admission::ETAT_VALIDE) {
                    /*** L'admission est validée ***/
                    // Création de la thèse à partir de l'Admission
                    $this->setCanCreateThese();
                    // Créer une TheseAnneeUniv
                    $this->setCanCreateTheseAnneeUniv();
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
                $this->foundThese = $theses[0];
                $this->addMessage(
                    self::PB_LE_DOCTORANT_POSSEDE_PLUSIEURS_THESES_EN_COURS . " L'année universitaire ne sera pas créée/màj.",
                    $this->messageSeverityForWarning
                );
            } else {
                /*** Une seule thèse : cool ***/
                $this->foundThese = $theses[0];
                $annee = $this->inscriptionAdministrative->getPeriodeAnneeUniversitaire();
                $theseAnneeUnivExistante =
                    $this->theseAnneeUnivService->getRepository()->findOneTheseAnneeUnivForTheseAndAnnee($this->foundThese, $annee);
                if ($theseAnneeUnivExistante !== null) {
                    /*** Une année universitaire existe déjà pour cette thèse et cette année ***/
                    if ($theseAnneeUnivExistante->getInscriptionAdministrative() !== $this->inscriptionAdministrative) {
                        /*** L'IA liée est null ou différente ***/
                        /*** NB : on décide de ne pas distinguer les cas 'IA liée différente' et 'Aucune IA liée'. ***/
                        // Créer le lien TheseAnneeUniv-IA
                        $this->setCanLinkTheseAnneeUnivToInscriptionAdministrative($theseAnneeUnivExistante);
                    }
                } else {
                    /*** Pas d'année universitaire trouvée ***/
                    // Créer une TheseAnneeUniv
                    $this->setCanCreateTheseAnneeUniv();
                }
            }
        }

        // Créer le lien IA-Doctorant si besoin
        $this->setCanCreateLinkInscriptionAdministrativeDoctorant();
    }

    /**
     * Cas de figure où aucun doctorant n'existe avec le code apprenant figurant dans l'IA.
     */
    private function processDoctorantInconnuAvecCodeApprenant(): void
    {
        // doctorant inconnu => thèse inconnue
        $this->foundThese = null;

        // Admission existante avec ce numéro candidat ?
        // (Remarque : Un numéro candidat est forcément présent, d'après le contrat d'interface Pégase.)
        $this->foundAdmission = $this->admissionService->getRepository()->findOneByNumeroCandidature($this->inscriptionAdministrative->getNoCandidat());

        if ($this->foundAdmission !== null) {
            /*** Admission trouvée avec ce numéro candidat ***/
            if ($this->foundAdmission->getEtat()->getCode() !== Admission::ETAT_VALIDE) {
                /*** L'admission n'est pas validée ***/
                // C'est anormal : rejet de l'IA !
                throw new InvalidArgumentException("Admission non encore validée !");
            }

            // Admission déjà liée à un doctorant ?
            if (($doctorant = $this->foundAdmission->getDoctorant()) !== null) {
                /*** Admission déjà liée à un doctorant ***/
                // Créer le lien IA-Doctorant si besoin
                $this->foundDoctorant = $doctorant;
                $this->setCanCreateLinkInscriptionAdministrativeDoctorant();
            } else {
                /*** Admission non liée à un doctorant ***/
                // Créer Doctorant
                $this->setCanCreateDoctorant();
                // Créer These
                $this->setCanCreateThese();
                // Créer une TheseAnneeUniv
                $this->setCanCreateTheseAnneeUniv();
                // Lier Admission au Doctorant
                $this->setCanLinkAdmissionToDoctorant();
            }
        } else {
            /*** Aucune Admission trouvée avec ce numéro candidat ***/
            // Doctorant existant avec cet INE ?
            $this->foundDoctorant = $this->doctorantService->getRepository()->findOneByIne(
                $this->inscriptionAdministrativeApprenant->getIne()
            );

            if ($this->foundDoctorant !== null) {
                /*** Doctorant trouvé avec cet INE ***/
                // (Remarque : Le doctorant peut avoir une thèse ou pas.)
                // Renseigner le code apprenant du doctorant
                $this->setCanUpdateCodeApprenantDoctorantFromApprenant();
                // Mise à jour du doctorant à partir des données Apprenant
                $this->setCanUpdateDoctorant();
                // Créer le lien IA-Doctorant
                $this->setCanCreateLinkInscriptionAdministrativeDoctorant();
            } else {
                /*** Aucun Doctorant trouvé avec cet INE ***/
                // (Remarque : Pas possible de créer la Thèse sans Admission ! Elle devra être créée manuellement.)
                // Créer Doctorant
                $this->setCanCreateDoctorant();
            }
        }

        // Créer le lien IA-Doctorant si besoin
        $this->setCanCreateLinkInscriptionAdministrativeDoctorant();
    }

    private function setCanCreateDoctorant(): void
    {
        $message = "Création possible d'un doctorant à partir des données sur l'apprenant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateDoctorant = true;
    }

    private function setCanUpdateDoctorant(): void
    {
        $diff = $this->computeDoctorantApprenantDiff($this->foundDoctorant, $this->inscriptionAdministrativeApprenant);

        if (empty($diff)) {
            // diff vide <=> màj inutile
            return;
        }

        $message = "Mise à jour possible du doctorant à partir des données de l'apprenant : " . PHP_EOL;
        $message .= implode(', ' . PHP_EOL, $diff) . PHP_EOL;
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canUpdateDoctorant = true;
    }

    private function setCanCreateThese(): void
    {
        $message = "Création possible d'une nouvelle thèse pour le doctorant à partir du dossier d'admission.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateThese = true;
    }

    private function setCanCreateTheseAnneeUniv(): void
    {
        $message = sprintf(
            "Création possible de l'année universitaire %s pour la thèse, à partir de la période de l'inscription administrative.",
            AnneeUniv::fromPremiereAnnee($this->inscriptionAdministrative->getPeriodeAnneeUniversitaire())
        );
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateTheseAnneeUniv = true;
    }

    private function setCanLinkTheseAnneeUnivToInscriptionAdministrative(TheseAnneeUniv $theseAnneeUniv): void
    {
        $message = sprintf(
            "Création possible du lien entre l'année universitaire %s de la thèse %s et l'inscription administrative.",
            $theseAnneeUniv,
            $theseAnneeUniv->getThese()
        );
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->foundTheseAnneeUniv = $theseAnneeUniv;
        $this->canLinkTheseAnneeUnivToInscriptionAdministrative = true;
    }

    private function setCanUpdateCodeApprenantDoctorantFromApprenant(): void
    {
        $codeApprenant = $this->inscriptionAdministrativeApprenant->getCode();

        $message = sprintf(
            "Enregistrement possible du code apprenant %s sur le doctorant.",
            $codeApprenant,
        );
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canUpdateCodeApprenantDoctorant = true;
    }

    private function setCanCreateLinkInscriptionAdministrativeDoctorant(): void
    {
        // si le lien existe déjà, noop
        if ($this->inscriptionAdministrativeDoctorant !== null) {
            return;
        }

        $message = "Création possible du lien entre cette inscription administrative et le doctorant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canCreateLinkInscriptionAdministrativeDoctorant = true;
    }

    private function setCanLinkAdmissionToDoctorant(): void
    {
        $message = "Création possible du lien entre l'admission et le doctorant.";
        $this->addMessage($message, $this->messageSeverityForAction);

        $this->canLinkAdmissionToDoctorant = true;
    }

    protected function computeDoctorantApprenantDiff(Doctorant $doctorant, InscriptionAdministrativeApprenant $apprenant): array
    {
        $diffIndividu = $this->individuService->diffIndividuFromApprenant($doctorant->getIndividu(), $apprenant);
        $diffDoctorant = $this->doctorantService->diffDoctorantFromApprenant($doctorant, $apprenant);

        if (empty($diffIndividu) && empty($diffDoctorant)) {
            return [];
        }

        $diff = [];

        if (!empty($diffIndividu)) {
            $diff[] = implode(', '. PHP_EOL, array_map(
                fn($field) => "individu.".$field.' : '.$diffIndividu[$field][0].' => '.$diffIndividu[$field][1],
                array_keys($diffIndividu)
            ));
        }
        if (!empty($diffDoctorant)) {
            $diff[] = implode(', '. PHP_EOL, array_map(
                fn($field) => "doctorant.".$field.' : '.$diffDoctorant[$field][0].' => '.$diffDoctorant[$field][1],
                array_keys($diffDoctorant)
            ));
        }

        return $diff;
    }
}