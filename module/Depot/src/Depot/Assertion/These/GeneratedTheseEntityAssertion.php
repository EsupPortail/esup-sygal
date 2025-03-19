<?php

namespace Depot\Assertion\These;

use Application\Assertion\Exception\UnexpectedPrivilegeException as UnexpectedPrivilegeException;

/**
 * Classe mère d'Assertion.
 *
 * Générée avec la ligne de commande 'bin/assertions/generate-assertion -f
 * module/Depot/data/TheseEntityAssertion.csv'.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 19/03/2025 11:26:22
 */
abstract class GeneratedTheseEntityAssertion
{
    protected $failureMessage;

    protected $linesTrace = [
        
    ];

    /**
     * Retourne true si le privilège spécifié est accordé ; false sinon.
     *
     * @param string $privilege
     * @return bool
     */
    public function assertAsBoolean(string $privilege) : bool
    {
        $this->failureMessage = null;

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 4 */
            $this->linesTrace[] = '/* line 4 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if ($this->isTheseSoutenue() /* test 6 */) {
                $this->failureMessage = "Le dépôt initial n'est plus autorisé car la date de soutenance est passée.";
                return false;
            }
            /* line 6 */
            $this->linesTrace[] = '/* line 6 */';
            if (! $this->isExisteValidationPageDeCouverture() /* test 22 */) {
                $this->failureMessage = "La page de couverture n’a pas été validée.";
                return false;
            }
            /* line 7 */
            $this->linesTrace[] = '/* line 7 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.";
                return false;
            }
            /* line 8 */
            $this->linesTrace[] = '/* line 8 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "Opération impossible dès lors que la bibliothèque universitaire a validé.";
                return false;
            }
            /* line 9 */
            $this->linesTrace[] = '/* line 9 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_FICHIER_DIVERS_TELEVERSER) {
        //--------------------------------------------------------------------------------------
            /* line 10 */
            $this->linesTrace[] = '/* line 10 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_FICHIER_DIVERS_CONSULTER) {
        //--------------------------------------------------------------------------------------
            /* line 11 */
            $this->linesTrace[] = '/* line 11 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 12 */
            $this->linesTrace[] = '/* line 12 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 13 */
            $this->linesTrace[] = '/* line 13 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 14 */
            $this->linesTrace[] = '/* line 14 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "Opération impossible dès lors que la bibliothèque universitaire a validé.";
                return false;
            }
            /* line 15 */
            $this->linesTrace[] = '/* line 15 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 16 */
            $this->linesTrace[] = '/* line 16 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 17 */
            $this->linesTrace[] = '/* line 17 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 8 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée.";
                return false;
            }
            /* line 18 */
            $this->linesTrace[] = '/* line 18 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 18 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.";
                return false;
            }
            /* line 19 */
            $this->linesTrace[] = '/* line 19 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 20 */
            $this->linesTrace[] = '/* line 20 */';
            if (! $this->isTheseSoutenue() /* test 6 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 21 */
            $this->linesTrace[] = '/* line 21 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 22 */
            $this->linesTrace[] = '/* line 22 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 16 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 23 */
            $this->linesTrace[] = '/* line 23 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 24 */
            $this->linesTrace[] = '/* line 24 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 25 */
            $this->linesTrace[] = '/* line 25 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "La validation par la bibliothèque universitaire a été faite.";
                return false;
            }
            /* line 26 */
            $this->linesTrace[] = '/* line 26 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE) {
        //--------------------------------------------------------------------------------------
            /* line 27 */
            $this->linesTrace[] = '/* line 27 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 28 */
            $this->linesTrace[] = '/* line 28 */';
            if (! $this->isPageDeCouvertureGenerable() /* test 28 */) {
                $this->failureMessage = "Des informations sont manquantes pour pouvoir générer la page de couverture.";
                return false;
            }
            /* line 29 */
            $this->linesTrace[] = '/* line 29 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 30 */
            $this->linesTrace[] = '/* line 30 */';
            if (! $this->isExisteValidationPageDeCouverture() /* test 22 */) {
                $this->failureMessage = "La page de couverture n’a pas été validée.";
                return false;
            }
            /* line 31 */
            $this->linesTrace[] = '/* line 31 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 32 */
            $this->linesTrace[] = '/* line 32 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 33 */
            $this->linesTrace[] = '/* line 33 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "La validation par la bibliothèque universitaire a été faite.";
                return false;
            }
            /* line 34 */
            $this->linesTrace[] = '/* line 34 */';
            if (! $this->isAttestationsVersionInitialeSaisies() /* test 9 */) {
                $this->failureMessage = "Les attestations doivent être renseignées d’abord.";
                return false;
            }
            /* line 35 */
            $this->linesTrace[] = '/* line 35 */';
            if (! $this->isInfosBuSaisies() /* test 11 */) {
                $this->failureMessage = "La bibliothèque universitaire n'a pas renseigné toutes informations requises.";
                return false;
            }
            /* line 36 */
            $this->linesTrace[] = '/* line 36 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 37 */
            $this->linesTrace[] = '/* line 37 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 38 */
            $this->linesTrace[] = '/* line 38 */';
            if (! $this->isExisteValidationRdvBu() /* test 20 */) {
                return false;
            }
            /* line 39 */
            $this->linesTrace[] = '/* line 39 */';
            if ($this->isExisteFichierTheseVersionCorrigee() /* test 13 */ && 
                $this->isExisteValidationRdvBu() /* test 20 */) {
                return false;
            }
            /* line 40 */
            $this->linesTrace[] = '/* line 40 */';
            if (! $this->isExisteFichierTheseVersionCorrigee() /* test 13 */ && 
                $this->isExisteValidationRdvBu() /* test 20 */) {
                return true;
            }
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 41 */
            $this->linesTrace[] = '/* line 41 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse.";
                return false;
            }
            /* line 42 */
            $this->linesTrace[] = '/* line 42 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 16 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 43 */
            $this->linesTrace[] = '/* line 43 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 8 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée.";
                return false;
            }
            /* line 44 */
            $this->linesTrace[] = '/* line 44 */';
            if ($this->isCorrectionAttendue() /* test 7 */ && 
                ! $this->isDepotVersionCorrigeeValide() /* test 16 */ && 
                ! $this->isDateButoirDepotVersionCorrigeeDepassee() /* test 8 */) {
                return true;
            }
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 45 */
            $this->linesTrace[] = '/* line 45 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 18 */) {
                return false;
            }
            /* line 46 */
            $this->linesTrace[] = '/* line 46 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE) {
        //--------------------------------------------------------------------------------------
            /* line 47 */
            $this->linesTrace[] = '/* line 47 */';
            if (! $this->isExisteValidationDepotVersionCorrigee() /* test 19 */) {
                $this->failureMessage = "Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.";
                return false;
            }
            /* line 48 */
            $this->linesTrace[] = '/* line 48 */';
            if (! $this->isUtilisateurExisteParmiValidateursAttendus() /* test 25 */) {
                return false;
            }
            /* line 49 */
            $this->linesTrace[] = '/* line 49 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 50 */
            $this->linesTrace[] = '/* line 50 */';
            if (! $this->isUtilisateurExisteParmiValidateursAyantValide() /* test 26 */) {
                return false;
            }
            /* line 51 */
            $this->linesTrace[] = '/* line 51 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 52 */
            $this->linesTrace[] = '/* line 52 */';
            if ($this->isExisteValidationVersionPapierCorrigee() /* test 21 */) {
                return false;
            }
            /* line 53 */
            $this->linesTrace[] = '/* line 53 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 54 */
            $this->linesTrace[] = '/* line 54 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 55 */
            $this->linesTrace[] = '/* line 55 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 56 */
            $this->linesTrace[] = '/* line 56 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "Opération impossible dès lors que la bibliothèque universitaire a validé.";
                return false;
            }
            /* line 57 */
            $this->linesTrace[] = '/* line 57 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 58 */
            $this->linesTrace[] = '/* line 58 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 59 */
            $this->linesTrace[] = '/* line 59 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 16 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 60 */
            $this->linesTrace[] = '/* line 60 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 61 */
            $this->linesTrace[] = '/* line 61 */';
            if ($this->isAttestationsVersionCorrigeeSaisies() /* test 10 */) {
                $this->failureMessage = "Les attestations ont déjà été renseignées.";
                return false;
            }
            /* line 62 */
            $this->linesTrace[] = '/* line 62 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 63 */
            $this->linesTrace[] = '/* line 63 */';
            if (! $this->isExisteFichierTheseVersionOriginale() /* test 14 */) {
                $this->failureMessage = "Le dépôt d'une version initiale doit être fait au préalable.";
                return false;
            }
            /* line 64 */
            $this->linesTrace[] = '/* line 64 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 16 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 65 */
            $this->linesTrace[] = '/* line 65 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 66 */
            $this->linesTrace[] = '/* line 66 */';
            if ($this->isAttestationsVersionInitialeSaisies() /* test 9 */) {
                $this->failureMessage = "Les attestations ont déjà été renseignées.";
                return false;
            }
            /* line 67 */
            $this->linesTrace[] = '/* line 67 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 68 */
            $this->linesTrace[] = '/* line 68 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 69 */
            $this->linesTrace[] = '/* line 69 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "Opération impossible dès lors que la bibliothèque universitaire a validé.";
                return false;
            }
            /* line 70 */
            $this->linesTrace[] = '/* line 70 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 71 */
            $this->linesTrace[] = '/* line 71 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 72 */
            $this->linesTrace[] = '/* line 72 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 16 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 73 */
            $this->linesTrace[] = '/* line 73 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 74 */
            $this->linesTrace[] = '/* line 74 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 75 */
            $this->linesTrace[] = '/* line 75 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 76 */
            $this->linesTrace[] = '/* line 76 */';
            if ($this->isExisteValidationBU() /* test 17 */) {
                $this->failureMessage = "Opération impossible dès lors que la bibliothèque universitaire a validé.";
                return false;
            }
            /* line 77 */
            $this->linesTrace[] = '/* line 77 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_CORREC_AUTORISEE_ACCORDER_SURSIS) {
        //--------------------------------------------------------------------------------------
            /* line 78 */
            $this->linesTrace[] = '/* line 78 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 79 */
            $this->linesTrace[] = '/* line 79 */';
            if (! $this->isDateButoirDepotVersionCorrigeeDepassee() /* test 8 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée n’est pas dépassée (%s).";
                return false;
            }
            /* line 80 */
            $this->linesTrace[] = '/* line 80 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_CONSULTATION_CORREC_AUTORISEE_INFORMATIONS) {
        //--------------------------------------------------------------------------------------
            /* line 81 */
            $this->linesTrace[] = '/* line 81 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */) {
        //--------------------------------------------------------------------------------------
            /* line 82 */
            $this->linesTrace[] = '/* line 82 */';
            return true;
        }

        if ($privilege === \Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE /* évite UnexpectedPrivilegeException */) {
        //--------------------------------------------------------------------------------------
            /* line 83 */
            $this->linesTrace[] = '/* line 83 */';
            return true;
        }

        throw new UnexpectedPrivilegeException(
            "Le privilège spécifié n'est pas couvert par l'assertion: $privilege. Trace : " . PHP_EOL . implode(PHP_EOL, $this->linesTrace));
    }

    /**
     * @return bool
     */
    abstract protected function isTheseEnCours() : bool;
    /**
     * @return bool
     */
    abstract protected function isTheseSoutenue() : bool;
    /**
     * @return bool
     */
    abstract protected function isCorrectionAttendue() : bool;
    /**
     * @return bool
     */
    abstract protected function isDateButoirDepotVersionCorrigeeDepassee() : bool;
    /**
     * @return bool
     */
    abstract protected function isAttestationsVersionInitialeSaisies() : bool;
    /**
     * @return bool
     */
    abstract protected function isAttestationsVersionCorrigeeSaisies() : bool;
    /**
     * @return bool
     */
    abstract protected function isInfosBuSaisies() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteFichierTheseVersionCorrigee() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteFichierTheseVersionOriginale() : bool;
    /**
     * @return bool
     */
    abstract protected function isDepotVersionCorrigeeValide() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteValidationBU() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteValidationCorrectionsThese() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteValidationDepotVersionCorrigee() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteValidationRdvBu() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteValidationVersionPapierCorrigee() : bool;
    /**
     * @return bool
     */
    abstract protected function isExisteValidationPageDeCouverture() : bool;
    /**
     * @return bool
     */
    abstract protected function isUtilisateurEstAuteurDeLaThese() : bool;
    /**
     * @return bool
     */
    abstract protected function isUtilisateurExisteParmiValidateursAttendus() : bool;
    /**
     * @return bool
     */
    abstract protected function isUtilisateurExisteParmiValidateursAyantValide() : bool;
    /**
     * @return bool
     */
    abstract protected function isPageDeCouvertureGenerable() : bool;
    /**
     * Retourne le contenu du fichier CSV à partir duquel a été générée cette
     * classe.
     *
     * @return string
     */
    public function loadedFileContent() : string
    {
        return <<<'EOT'
class;Depot\Assertion\These\GeneratedTheseEntityAssertion;;5;6;7;8;9;10;11;12;13;14;15;16;17;18;19;20;21;22;23;24;25;26;27;28;;;
line;enabled;privilege;isTheseEnCours;isTheseSoutenue;isCorrectionAttendue;isDateButoirDepotVersionCorrigeeDepassee;isAttestationsVersionInitialeSaisies;isAttestationsVersionCorrigeeSaisies;isInfosBuSaisies;;isExisteFichierTheseVersionCorrigee;isExisteFichierTheseVersionOriginale;;isDepotVersionCorrigeeValide;isExisteValidationBU;isExisteValidationCorrectionsThese;isExisteValidationDepotVersionCorrigee;isExisteValidationRdvBu;isExisteValidationVersionPapierCorrigee;isExisteValidationPageDeCouverture;;isUtilisateurEstAuteurDeLaThese;isUtilisateurExisteParmiValidateursAttendus;isUtilisateurExisteParmiValidateursAyantValide;;isPageDeCouvertureGenerable;;return;message
4;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
5;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;;1:1;;;;;;;;;;;;;;;;;;;;;;;;0;Le dépôt initial n'est plus autorisé car la date de soutenance est passée.
6;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;;1:0;;;;;;;;0;La page de couverture n’a pas été validée.
7;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;;;1:1;;;;;;;;;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.
8;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la bibliothèque universitaire a validé.
9;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
10;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_FICHIER_DIVERS_TELEVERSER;;;;;;;;;;;;;;;;;;;;;;;;;;1;
11;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_FICHIER_DIVERS_CONSULTER;;;;;;;;;;;;;;;;;;;;;;;;;;1;
12;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
13;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;1:1;;;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
14;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la bibliothèque universitaire a validé.
15;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
16;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
17;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;1:1;;;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée.
18;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.
19;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
20;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;1:0;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
21;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
22;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
23;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
24;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_RDV_BU;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
25;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;La validation par la bibliothèque universitaire a été faite.
26;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;;;;;;;;;;;;;;1;
27;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
28;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;;;;;;;;;;;;;;;;;;;;1:0;;0;Des informations sont manquantes pour pouvoir générer la page de couverture.
29;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
30;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR;;;;;;;;;;;;;;;;;;1:0;;;;;;;;0;La page de couverture n’a pas été validée.
31;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;1;
32;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
33;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;La validation par la bibliothèque universitaire a été faite.
34;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Les attestations doivent être renseignées d’abord.
35;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;1:0;;;;;;;;;;;;;;;;;;;0;La bibliothèque universitaire n'a pas renseigné toutes informations requises.
36;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;;;;;;;;;;;;;;1;
37;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
38;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;;;;;;;1:0;;;;;;;;;;0;
39;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;1:1;;;;;;;2:1;;;;;;;;;;0;
40;1;\Depot\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;1:0;;;;;;;2:1;;;;;;;;;;1;
41;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse.
42;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
43;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;1:1;;;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée.
44;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;1:1;3:0;;;;;;;;2:0;;;;;;;;;;;;;;1;
45;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;1:1;;;;;;;;;;;;0;
46;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;1;
47;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;1:0;;;;;;;;;;;0;Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.
48;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;;;;;1:0;;;;;0;
49;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
50;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;;;;;;1:0;;;;0;
51;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;1;
52;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;1:1;;;;;;;;;0;
53;1;\Depot\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
54;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
55;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;1:1;;;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
56;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la bibliothèque universitaire a validé.
57;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
58;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
59;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
60;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
61;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;0;Les attestations ont déjà été renseignées.
62;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
63;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;1:0;;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale doit être fait au préalable.
64;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
65;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
66;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Les attestations ont déjà été renseignées.
67;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
68;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;1:1;;;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
69;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la bibliothèque universitaire a validé.
70;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
71;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
72;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;2:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
73;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
74;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
75;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;1:1;;;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
76;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la bibliothèque universitaire a validé.
77;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;1;
78;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_CORREC_AUTORISEE_ACCORDER_SURSIS;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
79;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_CORREC_AUTORISEE_ACCORDER_SURSIS;;;;1:0;;;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée n’est pas dépassée (%s).
80;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_CORREC_AUTORISEE_ACCORDER_SURSIS;;;;;;;;;;;;;;;;;;;;;;;;;;1;
81;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_CONSULTATION_CORREC_AUTORISEE_INFORMATIONS;;;;;;;;;;;;;;;;;;;;;;;;;;1;
82;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;;;;;;;;;;1;
83;1;\Depot\Provider\Privilege\DepotPrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;;;;;;;;;;1;
EOT;
    }
}
