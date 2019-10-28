<?php

namespace Application\Assertion\These;

/**
 * Classe mère d'Assertion.
 *
 * Générée à partir du fichier
 * /home/metivier/MyWeb/sygal/data/assertions/TheseEntityAssertion.csv.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 24/10/2019 10:08:24
 */
abstract class GeneratedTheseEntityAssertion
{

    protected $failureMessage = null;

    protected $linesTrace = array(
        
    );

    /**
     * Retourne true si le privilège spécifié est accordé ; false sinon.
     *
     * @param string $privilege
     * @return bool
     */
    public function assertAsBoolean($privilege)
    {
        $this->failureMessage = null;

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE) {
        //--------------------------------------------------------------------------------------
            /* line 1 */
            $this->linesTrace[] = '/* line 1 */';
            return true;
        }

        if ($privilege) {
        //--------------------------------------------------------------------------------------
            /* line 2 */
            $this->linesTrace[] = '/* line 2 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 22 */) {
                $this->failureMessage = "Cette thèse n'est pas la vôtre.";
                return false;
            }
            /* line 3 */
            $this->linesTrace[] = '/* line 3 */';
            if (! $this->isStructureDuRoleRespectee() /* test 3 */) {
                $this->failureMessage = "Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.";
                return false;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE) {
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
            if (! $this->isExisteValidationPageDeCouverture() /* test 20 */) {
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
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 9 */
            $this->linesTrace[] = '/* line 9 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_FICHIER_DIVERS_TELEVERSER) {
        //--------------------------------------------------------------------------------------
            /* line 10 */
            $this->linesTrace[] = '/* line 10 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_FICHIER_DIVERS_CONSULTER) {
        //--------------------------------------------------------------------------------------
            /* line 11 */
            $this->linesTrace[] = '/* line 11 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE) {
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
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 15 */
            $this->linesTrace[] = '/* line 15 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE) {
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
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée (%s).";
                return false;
            }
            /* line 18 */
            $this->linesTrace[] = '/* line 18 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 16 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.";
                return false;
            }
            /* line 19 */
            $this->linesTrace[] = '/* line 19 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE) {
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
            if ($this->isDepotVersionCorrigeeValide() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 23 */
            $this->linesTrace[] = '/* line 23 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 24 */
            $this->linesTrace[] = '/* line 24 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 25 */
            $this->linesTrace[] = '/* line 25 */';
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "La validation par la BU a été faite.";
                return false;
            }
            /* line 26 */
            $this->linesTrace[] = '/* line 26 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE) {
        //--------------------------------------------------------------------------------------
            /* line 27 */
            $this->linesTrace[] = '/* line 27 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 28 */
            $this->linesTrace[] = '/* line 28 */';
            if (! $this->isPageDeCouvertureGenerable() /* test 26 */) {
                $this->failureMessage = "Des informations sont manquantes pour pouvoir générer la page de couverture.";
                return false;
            }
            /* line 29 */
            $this->linesTrace[] = '/* line 29 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 30 */
            $this->linesTrace[] = '/* line 30 */';
            if (! $this->isExisteValidationPageDeCouverture() /* test 20 */) {
                $this->failureMessage = "La page de couverture n’a pas été validée.";
                return false;
            }
            /* line 31 */
            $this->linesTrace[] = '/* line 31 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 32 */
            $this->linesTrace[] = '/* line 32 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 33 */
            $this->linesTrace[] = '/* line 33 */';
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "La validation par la BU a été faite.";
                return false;
            }
            /* line 34 */
            $this->linesTrace[] = '/* line 34 */';
            if (! $this->isInfosBuSaisies() /* test 9 */) {
                $this->failureMessage = "La BU n'a pas renseigné toutes informations requises.";
                return false;
            }
            /* line 35 */
            $this->linesTrace[] = '/* line 35 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 36 */
            $this->linesTrace[] = '/* line 36 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 37 */
            $this->linesTrace[] = '/* line 37 */';
            if (! $this->isExisteValidationRdvBu() /* test 18 */) {
                return false;
            }
            /* line 38 */
            $this->linesTrace[] = '/* line 38 */';
            if ($this->isExisteFichierTheseVersionCorrigee() /* test 11 */ && 
                $this->isExisteValidationRdvBu() /* test 18 */) {
                return false;
            }
            /* line 39 */
            $this->linesTrace[] = '/* line 39 */';
            if (! $this->isExisteFichierTheseVersionCorrigee() /* test 11 */ && 
                $this->isExisteValidationRdvBu() /* test 18 */) {
                return true;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 40 */
            $this->linesTrace[] = '/* line 40 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse.";
                return false;
            }
            /* line 41 */
            $this->linesTrace[] = '/* line 41 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 42 */
            $this->linesTrace[] = '/* line 42 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 8 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée (%s).";
                return false;
            }
            /* line 43 */
            $this->linesTrace[] = '/* line 43 */';
            if ($this->isCorrectionAttendue() /* test 7 */ && 
                ! $this->isDepotVersionCorrigeeValide() /* test 14 */ && 
                ! $this->isDateButoirDepotVersionCorrigeeDepassee() /* test 8 */) {
                return true;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 44 */
            $this->linesTrace[] = '/* line 44 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 16 */) {
                return false;
            }
            /* line 45 */
            $this->linesTrace[] = '/* line 45 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE) {
        //--------------------------------------------------------------------------------------
            /* line 46 */
            $this->linesTrace[] = '/* line 46 */';
            if (! $this->isExisteValidationDepotVersionCorrigee() /* test 17 */) {
                $this->failureMessage = "Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.";
                return false;
            }
            /* line 47 */
            $this->linesTrace[] = '/* line 47 */';
            if (! $this->isUtilisateurExisteParmiValidateursAttendus() /* test 23 */) {
                return false;
            }
            /* line 48 */
            $this->linesTrace[] = '/* line 48 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 49 */
            $this->linesTrace[] = '/* line 49 */';
            if (! $this->isUtilisateurExisteParmiValidateursAyantValide() /* test 24 */) {
                return false;
            }
            /* line 50 */
            $this->linesTrace[] = '/* line 50 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 51 */
            $this->linesTrace[] = '/* line 51 */';
            if ($this->isExisteValidationVersionPapierCorrigee() /* test 19 */) {
                return false;
            }
            /* line 52 */
            $this->linesTrace[] = '/* line 52 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 53 */
            $this->linesTrace[] = '/* line 53 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 54 */
            $this->linesTrace[] = '/* line 54 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 55 */
            $this->linesTrace[] = '/* line 55 */';
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 56 */
            $this->linesTrace[] = '/* line 56 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 57 */
            $this->linesTrace[] = '/* line 57 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 58 */
            $this->linesTrace[] = '/* line 58 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 59 */
            $this->linesTrace[] = '/* line 59 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 60 */
            $this->linesTrace[] = '/* line 60 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 61 */
            $this->linesTrace[] = '/* line 61 */';
            if (! $this->isExisteFichierTheseVersionOriginale() /* test 12 */) {
                $this->failureMessage = "Le dépôt d'une version initiale doit être fait au préalable.";
                return false;
            }
            /* line 62 */
            $this->linesTrace[] = '/* line 62 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 63 */
            $this->linesTrace[] = '/* line 63 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 64 */
            $this->linesTrace[] = '/* line 64 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 65 */
            $this->linesTrace[] = '/* line 65 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 66 */
            $this->linesTrace[] = '/* line 66 */';
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 67 */
            $this->linesTrace[] = '/* line 67 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 68 */
            $this->linesTrace[] = '/* line 68 */';
            if (! $this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 69 */
            $this->linesTrace[] = '/* line 69 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 70 */
            $this->linesTrace[] = '/* line 70 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 71 */
            $this->linesTrace[] = '/* line 71 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 72 */
            $this->linesTrace[] = '/* line 72 */';
            if ($this->isCorrectionAttendue() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 73 */
            $this->linesTrace[] = '/* line 73 */';
            if ($this->isExisteValidationBU() /* test 15 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 74 */
            $this->linesTrace[] = '/* line 74 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */) {
        //--------------------------------------------------------------------------------------
            /* line 75 */
            $this->linesTrace[] = '/* line 75 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE /* évite UnexpectedPrivilegeException */) {
        //--------------------------------------------------------------------------------------
            /* line 76 */
            $this->linesTrace[] = '/* line 76 */';
            return true;
        }

        throw new \Application\Assertion\Exception\UnexpectedPrivilegeException(
            "Le privilège spécifié n'est pas couvert par l'assertion: $privilege. Trace : " . PHP_EOL . implode(PHP_EOL, $this->linesTrace));
    }

    /**
     * @return bool
     */
    abstract protected function isRoleDoctorantSelected();
    /**
     * @return bool
     */
    abstract protected function isStructureDuRoleRespectee();
    /**
     * @return bool
     */
    abstract protected function isTheseEnCours();
    /**
     * @return bool
     */
    abstract protected function isTheseSoutenue();
    /**
     * @return bool
     */
    abstract protected function isCorrectionAttendue();
    /**
     * @return bool
     */
    abstract protected function isDateButoirDepotVersionCorrigeeDepassee();
    /**
     * @return bool
     */
    abstract protected function isInfosBuSaisies();
    /**
     * @return bool
     */
    abstract protected function isExisteFichierTheseVersionCorrigee();
    /**
     * @return bool
     */
    abstract protected function isExisteFichierTheseVersionOriginale();
    /**
     * @return bool
     */
    abstract protected function isDepotVersionCorrigeeValide();
    /**
     * @return bool
     */
    abstract protected function isExisteValidationBU();
    /**
     * @return bool
     */
    abstract protected function isExisteValidationCorrectionsThese();
    /**
     * @return bool
     */
    abstract protected function isExisteValidationDepotVersionCorrigee();
    /**
     * @return bool
     */
    abstract protected function isExisteValidationRdvBu();
    /**
     * @return bool
     */
    abstract protected function isExisteValidationVersionPapierCorrigee();
    /**
     * @return bool
     */
    abstract protected function isExisteValidationPageDeCouverture();
    /**
     * @return bool
     */
    abstract protected function isUtilisateurEstAuteurDeLaThese();
    /**
     * @return bool
     */
    abstract protected function isUtilisateurExisteParmiValidateursAttendus();
    /**
     * @return bool
     */
    abstract protected function isUtilisateurExisteParmiValidateursAyantValide();
    /**
     * @return bool
     */
    abstract protected function isPageDeCouvertureGenerable();
    /**
     * Retourne le contenu du fichier CSV à partir duquel a été générée cette
     * classe.
     *
     * @return string
     */
    public function loadedFileContent()
    {
        return <<<'EOT'
class;Application\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16;17;18;19;20;21;22;23;24;25;26;;;
line;enabled;privilege;isRoleDoctorantSelected;;isStructureDuRoleRespectee;;isTheseEnCours;isTheseSoutenue;isCorrectionAttendue;isDateButoirDepotVersionCorrigeeDepassee;isInfosBuSaisies;;isExisteFichierTheseVersionCorrigee;isExisteFichierTheseVersionOriginale;;isDepotVersionCorrigeeValide;isExisteValidationBU;isExisteValidationCorrectionsThese;isExisteValidationDepotVersionCorrigee;isExisteValidationRdvBu;isExisteValidationVersionPapierCorrigee;isExisteValidationPageDeCouverture;;isUtilisateurEstAuteurDeLaThese;isUtilisateurExisteParmiValidateursAttendus;isUtilisateurExisteParmiValidateursAyantValide;;isPageDeCouvertureGenerable;;return;message
1;1;\Application\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
2;1;*;1:1;;;;;;;;;;;;;;;;;;;;;2:0;;;;;;0;Cette thèse n'est pas la vôtre.
3;1;*;;;1:0;;;;;;;;;;;;;;;;;;;;;;;;;0;Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.
4;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
5;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;;0;Le dépôt initial n'est plus autorisé car la date de soutenance est passée.
6;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;1:0;;;;;;;;0;La page de couverture n’a pas été validée.
7;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.
8;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
9;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
10;1;\Application\Provider\Privilege\ThesePrivileges::THESE_FICHIER_DIVERS_TELEVERSER;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
11;1;\Application\Provider\Privilege\ThesePrivileges::THESE_FICHIER_DIVERS_CONSULTER;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
12;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
13;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
14;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
15;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
16;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
17;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée (%s).
18;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.
19;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
20;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
21;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
22;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
23;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
24;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
25;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;La validation par la BU a été faite.
26;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
27;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
28;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;;;;;;;;;;;;;;;;;;;;;;1:0;;0;Des informations sont manquantes pour pouvoir générer la page de couverture.
29;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
30;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR;;;;;;;;;;;;;;;;;;;;1:0;;;;;;;;0;La page de couverture n’a pas été validée.
31;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
32;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
33;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;La validation par la BU a été faite.
34;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;1:0;;;;;;;;;;;;;;;;;;;0;La BU n'a pas renseigné toutes informations requises.
35;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
36;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
37;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;;;;;;;;;1:0;;;;;;;;;;0;
38;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;;1:1;;;;;;;2:1;;;;;;;;;;0;
39;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;;1:0;;;;;;;2:1;;;;;;;;;;1;
40;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse.
41;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
42;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée (%s).
43;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;1:1;3:0;;;;;;2:0;;;;;;;;;;;;;;1;
44;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;0;
45;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
46;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;1:0;;;;;;;;;;;0;Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.
47;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;;;;;;;1:0;;;;;0;
48;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
49;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;1:0;;;;0;
50;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
51;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;;;1:1;;;;;;;;;0;
52;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
53;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
54;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
55;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
56;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
57;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
58;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
59;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
60;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
61;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;1:0;;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale doit être fait au préalable.
62;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
63;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
64;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
65;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
66;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
67;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
68;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
69;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;;;2:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
70;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
71;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;1:0;;;;;;;;;;;;;;;;;;;;;;;0;L’état de la thèse ne permet pas cette opération.
72;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
73;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
74;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
75;1;\Application\Provider\Privilege\ThesePrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
76;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
EOT;
    }


}
