<?php

namespace Application\Assertion\These;

/**
 * Classe mère d'Assertion.
 *
 * Générée à partir du fichier
 * /home/gauthierb/workspace/sygal/data/assertions/TheseEntityAssertion.csv.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 11/07/2018 09:40:52
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
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 21 */) {
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
            if ($this->isTheseSoutenue() /* test 5 */) {
                $this->failureMessage = "Le dépôt initial n'est plus autorisé car la date de soutenance est passée.";
                return false;
            }
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if (! $this->isExisteValidationPageDeCouverture() /* test 19 */) {
                $this->failureMessage = "La page de couverture n’a pas été validée.";
                return false;
            }
            /* line 6 */
            $this->linesTrace[] = '/* line 6 */';
            if ($this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.";
                return false;
            }
            /* line 7 */
            $this->linesTrace[] = '/* line 7 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 8 */
            $this->linesTrace[] = '/* line 8 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 9 */
            $this->linesTrace[] = '/* line 9 */';
            if ($this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 10 */
            $this->linesTrace[] = '/* line 10 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 11 */
            $this->linesTrace[] = '/* line 11 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 12 */
            $this->linesTrace[] = '/* line 12 */';
            if (! $this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 13 */
            $this->linesTrace[] = '/* line 13 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 7 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée (%s).";
                return false;
            }
            /* line 14 */
            $this->linesTrace[] = '/* line 14 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 15 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.";
                return false;
            }
            /* line 15 */
            $this->linesTrace[] = '/* line 15 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 16 */
            $this->linesTrace[] = '/* line 16 */';
            if (! $this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 17 */
            $this->linesTrace[] = '/* line 17 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 13 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 18 */
            $this->linesTrace[] = '/* line 18 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 19 */
            $this->linesTrace[] = '/* line 19 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "La validation par la BU a été faite.";
                return false;
            }
            /* line 20 */
            $this->linesTrace[] = '/* line 20 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE) {
        //--------------------------------------------------------------------------------------
            /* line 21 */
            $this->linesTrace[] = '/* line 21 */';
            if (! $this->isPageDeCouvertureGenerable() /* test 25 */) {
                $this->failureMessage = "Des informations sont manquantes pour pouvoir générer la page de couverture.";
                return false;
            }
            /* line 22 */
            $this->linesTrace[] = '/* line 22 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 23 */
            $this->linesTrace[] = '/* line 23 */';
            if (! $this->isExisteValidationPageDeCouverture() /* test 19 */) {
                $this->failureMessage = "La page de couverture n’a pas été validée.";
                return false;
            }
            /* line 24 */
            $this->linesTrace[] = '/* line 24 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 25 */
            $this->linesTrace[] = '/* line 25 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "La validation par la BU a été faite.";
                return false;
            }
            /* line 26 */
            $this->linesTrace[] = '/* line 26 */';
            if (! $this->isInfosBuSaisies() /* test 8 */) {
                $this->failureMessage = "La BU n'a pas renseigné toutes informations requises.";
                return false;
            }
            /* line 27 */
            $this->linesTrace[] = '/* line 27 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 28 */
            $this->linesTrace[] = '/* line 28 */';
            if (! $this->isExisteValidationRdvBu() /* test 17 */) {
                return false;
            }
            /* line 29 */
            $this->linesTrace[] = '/* line 29 */';
            if ($this->isExisteFichierTheseVersionCorrigee() /* test 10 */ && 
                $this->isExisteValidationRdvBu() /* test 17 */) {
                return false;
            }
            /* line 30 */
            $this->linesTrace[] = '/* line 30 */';
            if (! $this->isExisteFichierTheseVersionCorrigee() /* test 10 */ && 
                $this->isExisteValidationRdvBu() /* test 17 */) {
                return true;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 31 */
            $this->linesTrace[] = '/* line 31 */';
            if (! $this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse.";
                return false;
            }
            /* line 32 */
            $this->linesTrace[] = '/* line 32 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 13 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 33 */
            $this->linesTrace[] = '/* line 33 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 7 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée (%s).";
                return false;
            }
            /* line 34 */
            $this->linesTrace[] = '/* line 34 */';
            if ($this->isCorrectionAttendue() /* test 6 */ && 
                ! $this->isDepotVersionCorrigeeValide() /* test 13 */ && 
                ! $this->isDateButoirDepotVersionCorrigeeDepassee() /* test 7 */) {
                return true;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 35 */
            $this->linesTrace[] = '/* line 35 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 15 */) {
                return false;
            }
            /* line 36 */
            $this->linesTrace[] = '/* line 36 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE) {
        //--------------------------------------------------------------------------------------
            /* line 37 */
            $this->linesTrace[] = '/* line 37 */';
            if (! $this->isExisteValidationDepotVersionCorrigee() /* test 16 */) {
                $this->failureMessage = "Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.";
                return false;
            }
            /* line 38 */
            $this->linesTrace[] = '/* line 38 */';
            if (! $this->isUtilisateurExisteParmiValidateursAttendus() /* test 22 */) {
                return false;
            }
            /* line 39 */
            $this->linesTrace[] = '/* line 39 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 40 */
            $this->linesTrace[] = '/* line 40 */';
            if (! $this->isUtilisateurExisteParmiValidateursAyantValide() /* test 23 */) {
                return false;
            }
            /* line 41 */
            $this->linesTrace[] = '/* line 41 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 42 */
            $this->linesTrace[] = '/* line 42 */';
            if ($this->isExisteValidationVersionPapierCorrigee() /* test 18 */) {
                return false;
            }
            /* line 43 */
            $this->linesTrace[] = '/* line 43 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 44 */
            $this->linesTrace[] = '/* line 44 */';
            if ($this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 45 */
            $this->linesTrace[] = '/* line 45 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 46 */
            $this->linesTrace[] = '/* line 46 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 47 */
            $this->linesTrace[] = '/* line 47 */';
            if (! $this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 48 */
            $this->linesTrace[] = '/* line 48 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 13 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 49 */
            $this->linesTrace[] = '/* line 49 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 50 */
            $this->linesTrace[] = '/* line 50 */';
            if (! $this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 51 */
            $this->linesTrace[] = '/* line 51 */';
            if (! $this->isExisteFichierTheseVersionOriginale() /* test 11 */) {
                $this->failureMessage = "Le dépôt d'une version initiale doit être fait au préalable.";
                return false;
            }
            /* line 52 */
            $this->linesTrace[] = '/* line 52 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 13 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 53 */
            $this->linesTrace[] = '/* line 53 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 54 */
            $this->linesTrace[] = '/* line 54 */';
            if ($this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 55 */
            $this->linesTrace[] = '/* line 55 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 56 */
            $this->linesTrace[] = '/* line 56 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 57 */
            $this->linesTrace[] = '/* line 57 */';
            if (! $this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 58 */
            $this->linesTrace[] = '/* line 58 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 13 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 59 */
            $this->linesTrace[] = '/* line 59 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 60 */
            $this->linesTrace[] = '/* line 60 */';
            if ($this->isCorrectionAttendue() /* test 6 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 61 */
            $this->linesTrace[] = '/* line 61 */';
            if ($this->isExisteValidationBU() /* test 14 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 63 */
            $this->linesTrace[] = '/* line 63 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */) {
        //--------------------------------------------------------------------------------------
            /* line 65 */
            $this->linesTrace[] = '/* line 65 */';
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
class;Application\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16;17;18;19;20;21;22;23;24;25;;;
line;enabled;privilege;isRoleDoctorantSelected;;isStructureDuRoleRespectee;;isTheseSoutenue;isCorrectionAttendue;isDateButoirDepotVersionCorrigeeDepassee;isInfosBuSaisies;;isExisteFichierTheseVersionCorrigee;isExisteFichierTheseVersionOriginale;;isDepotVersionCorrigeeValide;isExisteValidationBU;isExisteValidationCorrectionsThese;isExisteValidationDepotVersionCorrigee;isExisteValidationRdvBu;isExisteValidationVersionPapierCorrigee;isExisteValidationPageDeCouverture;;isUtilisateurEstAuteurDeLaThese;isUtilisateurExisteParmiValidateursAttendus;isUtilisateurExisteParmiValidateursAyantValide;;isPageDeCouvertureGenerable;;return;message
1;1;\Application\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
2;1;*;1:1;;;;;;;;;;;;;;;;;;;;2:0;;;;;;0;Cette thèse n'est pas la vôtre.
3;1;*;;;1:0;;;;;;;;;;;;;;;;;;;;;;;;0;Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.
4;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;1:1;;;;;;;;;;;;;;;;;;;;;;0;Le dépôt initial n'est plus autorisé car la date de soutenance est passée.
5;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;1:0;;;;;;;;0;La page de couverture n’a pas été validée.
6;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.
7;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
8;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
9;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
10;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
11;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
12;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
13;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée (%s).
14;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.
15;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
16;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
17;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
18;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
19;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;La validation par la BU a été faite.
20;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
21;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;;;;;;;;;;;;;;;;;;;;;1:0;;0;Des informations sont manquantes pour pouvoir générer la page de couverture.
22;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
23;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR;;;;;;;;;;;;;;;;;;;1:0;;;;;;;;0;La page de couverture n’a pas été validée.
24;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
25;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;La validation par la BU a été faite.
26;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;1:0;;;;;;;;;;;;;;;;;;;0;La BU n'a pas renseigné toutes informations requises.
27;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
28;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;;;;;;;;1:0;;;;;;;;;;0;
29;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;1:1;;;;;;;2:1;;;;;;;;;;0;
30;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;1:0;;;;;;;2:1;;;;;;;;;;1;
31;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse.
32;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
33;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;;1:1;;;;;;;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée (%s).
34;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;;;1:1;3:0;;;;;;2:0;;;;;;;;;;;;;;1;
35;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;;1:1;;;;;;;;;;;;0;
36;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
37;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;1:0;;;;;;;;;;;0;Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.
38;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;;;;;;1:0;;;;;0;
39;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
40;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;1:0;;;;0;
41;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
42;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;;1:1;;;;;;;;;0;
43;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
44;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
45;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
46;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
47;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
48;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
49;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
50;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
51;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;1:0;;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale doit être fait au préalable.
52;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;;1:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
53;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
54;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
55;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
56;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
57;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;1:0;;;;;;;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
58;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;;2:1;;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
59;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
60;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;1:1;;;;;;;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
61;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
63;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
65;1;\Application\Provider\Privilege\ThesePrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;;;;;;;;;;;1;
EOT;
    }


}
