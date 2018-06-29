<?php

namespace Application\Assertion\These;

/**
 * Classe mère d'Assertion.
 *
 * Générée à partir du fichier
 * /home/gauthierb/workspace/sygal/data/assertions/TheseEntityAssertion.csv.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 29/06/2018 10:48:15
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
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 14 */) {
                $this->failureMessage = "Cette thèse n'est pas la vôtre.";
                return false;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 3 */
            $this->linesTrace[] = '/* line 3 */';
            if ($this->isTheseSoutenue() /* test 13 */) {
                $this->failureMessage = "Le dépôt initial n'est plus autorisé car la date de soutenance est passée.";
                return false;
            }
            /* line 4 */
            $this->linesTrace[] = '/* line 4 */';
            if ($this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.";
                return false;
            }
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 6 */
            $this->linesTrace[] = '/* line 6 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 0 */
            $this->linesTrace[] = '/* line 0 */';
            if ($this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 7 */
            $this->linesTrace[] = '/* line 7 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 8 */
            $this->linesTrace[] = '/* line 8 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 9 */
            $this->linesTrace[] = '/* line 9 */';
            if (! $this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 10 */
            $this->linesTrace[] = '/* line 10 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 3 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée (%s).";
                return false;
            }
            /* line 11 */
            $this->linesTrace[] = '/* line 11 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 8 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.";
                return false;
            }
            /* line 12 */
            $this->linesTrace[] = '/* line 12 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 13 */
            $this->linesTrace[] = '/* line 13 */';
            if (! $this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 14 */
            $this->linesTrace[] = '/* line 14 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 4 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 15 */
            $this->linesTrace[] = '/* line 15 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 16 */
            $this->linesTrace[] = '/* line 16 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "La validation par la BU a été faite.";
                return false;
            }
            /* line 17 */
            $this->linesTrace[] = '/* line 17 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU) {
        //--------------------------------------------------------------------------------------
            /* line 18 */
            $this->linesTrace[] = '/* line 18 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "La validation par la BU a été faite.";
                return false;
            }
            /* line 19 */
            $this->linesTrace[] = '/* line 19 */';
            if (! $this->isInfosBuSaisies() /* test 12 */) {
                $this->failureMessage = "La BU n'a pas renseigné toutes informations requises.";
                return false;
            }
            /* line 20 */
            $this->linesTrace[] = '/* line 20 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 21 */
            $this->linesTrace[] = '/* line 21 */';
            if (! $this->isExisteValidationRdvBu() /* test 10 */) {
                return false;
            }
            /* line 22 */
            $this->linesTrace[] = '/* line 22 */';
            if ($this->isExisteFichierTheseVersionCorrigee() /* test 5 */ && 
                $this->isExisteValidationRdvBu() /* test 10 */) {
                return false;
            }
            /* line 23 */
            $this->linesTrace[] = '/* line 23 */';
            if (! $this->isExisteFichierTheseVersionCorrigee() /* test 5 */ && 
                $this->isExisteValidationRdvBu() /* test 10 */) {
                return true;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 24 */
            $this->linesTrace[] = '/* line 24 */';
            if (! $this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse.";
                return false;
            }
            /* line 25 */
            $this->linesTrace[] = '/* line 25 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 4 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 26 */
            $this->linesTrace[] = '/* line 26 */';
            if ($this->isDateButoirDepotVersionCorrigeeDepassee() /* test 3 */) {
                $this->failureMessage = "La date butoir pour le dépôt de la version corrigée est dépassée (%s).";
                return false;
            }
            /* line 27 */
            $this->linesTrace[] = '/* line 27 */';
            if ($this->isCorrectionAttendue() /* test 2 */ && 
                ! $this->isDepotVersionCorrigeeValide() /* test 4 */ && 
                ! $this->isDateButoirDepotVersionCorrigeeDepassee() /* test 3 */) {
                return true;
            }
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 28 */
            $this->linesTrace[] = '/* line 28 */';
            if ($this->isExisteValidationCorrectionsThese() /* test 8 */) {
                return false;
            }
            /* line 29 */
            $this->linesTrace[] = '/* line 29 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE) {
        //--------------------------------------------------------------------------------------
            /* line 30 */
            $this->linesTrace[] = '/* line 30 */';
            if (! $this->isExisteValidationDepotVersionCorrigee() /* test 9 */) {
                $this->failureMessage = "Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.";
                return false;
            }
            /* line 31 */
            $this->linesTrace[] = '/* line 31 */';
            if (! $this->isUtilisateurExisteParmiValidateursAttendus() /* test 15 */) {
                return false;
            }
            /* line 32 */
            $this->linesTrace[] = '/* line 32 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR) {
        //--------------------------------------------------------------------------------------
            /* line 33 */
            $this->linesTrace[] = '/* line 33 */';
            if (! $this->isUtilisateurExisteParmiValidateursAyantValide() /* test 16 */) {
                return false;
            }
            /* line 34 */
            $this->linesTrace[] = '/* line 34 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 35 */
            $this->linesTrace[] = '/* line 35 */';
            if ($this->isExisteValidationVersionPapierCorrigee() /* test 11 */) {
                return false;
            }
            /* line 36 */
            $this->linesTrace[] = '/* line 36 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 0 */
            $this->linesTrace[] = '/* line 0 */';
            if ($this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 37 */
            $this->linesTrace[] = '/* line 37 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 38 */
            $this->linesTrace[] = '/* line 38 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 39 */
            $this->linesTrace[] = '/* line 39 */';
            if (! $this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 40 */
            $this->linesTrace[] = '/* line 40 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 4 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 41 */
            $this->linesTrace[] = '/* line 41 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 42 */
            $this->linesTrace[] = '/* line 42 */';
            if (! $this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 43 */
            $this->linesTrace[] = '/* line 43 */';
            if (! $this->isExisteFichierTheseVersionOriginale() /* test 6 */) {
                $this->failureMessage = "Le dépôt d'une version initiale doit être fait au préalable.";
                return false;
            }
            /* line 44 */
            $this->linesTrace[] = '/* line 44 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 4 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 45 */
            $this->linesTrace[] = '/* line 45 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 46 */
            $this->linesTrace[] = '/* line 46 */';
            if ($this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 47 */
            $this->linesTrace[] = '/* line 47 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 48 */
            $this->linesTrace[] = '/* line 48 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE) {
        //--------------------------------------------------------------------------------------
            /* line 49 */
            $this->linesTrace[] = '/* line 49 */';
            if (! $this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Aucune correction n'est attendue pour cette thèse";
                return false;
            }
            /* line 50 */
            $this->linesTrace[] = '/* line 50 */';
            if ($this->isDepotVersionCorrigeeValide() /* test 4 */) {
                $this->failureMessage = "Opération impossible dès lors que le dépôt de la version corrigée a été validé.";
                return false;
            }
            /* line 51 */
            $this->linesTrace[] = '/* line 51 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE) {
        //--------------------------------------------------------------------------------------
            /* line 52 */
            $this->linesTrace[] = '/* line 52 */';
            if ($this->isCorrectionAttendue() /* test 2 */) {
                $this->failureMessage = "Opération impossible dès lors qu’une version corrigée est attendue.";
                return false;
            }
            /* line 53 */
            $this->linesTrace[] = '/* line 53 */';
            if ($this->isExisteValidationBU() /* test 7 */) {
                $this->failureMessage = "Opération impossible dès lors que la BU a validé.";
                return false;
            }
            /* line 55 */
            $this->linesTrace[] = '/* line 55 */';
            return true;
        }

        if ($privilege === \Application\Provider\Privilege\ThesePrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */) {
        //--------------------------------------------------------------------------------------
            /* line 57 */
            $this->linesTrace[] = '/* line 57 */';
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
    abstract protected function isCorrectionAttendue();
    /**
     * @return bool
     */
    abstract protected function isDateButoirDepotVersionCorrigeeDepassee();
    /**
     * @return bool
     */
    abstract protected function isDepotVersionCorrigeeValide();
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
    abstract protected function isInfosBuSaisies();
    /**
     * @return bool
     */
    abstract protected function isTheseSoutenue();
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
     * Retourne le contenu du fichier CSV à partir duquel a été générée cette
     * classe.
     *
     * @return string
     */
    public function loadedFileContent()
    {
        return <<<'EOT'
class;Application\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16;;
line;enabled;privilege;isRoleDoctorantSelected;isCorrectionAttendue;isDateButoirDepotVersionCorrigeeDepassee;isDepotVersionCorrigeeValide;isExisteFichierTheseVersionCorrigee;isExisteFichierTheseVersionOriginale;isExisteValidationBU;isExisteValidationCorrectionsThese;isExisteValidationDepotVersionCorrigee;isExisteValidationRdvBu;isExisteValidationVersionPapierCorrigee;isInfosBuSaisies;isTheseSoutenue;isUtilisateurEstAuteurDeLaThese;isUtilisateurExisteParmiValidateursAttendus;isUtilisateurExisteParmiValidateursAyantValide;return;message
1;1;\Application\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE;;;;;;;;;;;;;;;;;1;
2;1;*;1:1;;;;;;;;;;;;;2:0;;;0;Cette thèse n'est pas la vôtre.
3;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;1:1;;;;0;Le dépôt initial n'est plus autorisé car la date de soutenance est passée.
4;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;1:1;;;;;;;;;;;;;;;0;Le dépôt d'une version initiale n'est plus possible dès lors qu'une version corrigée est attendue.
5;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
6;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_INITIALE;;;;;;;;;;;;;;;;;1;
        ;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;1:1;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
7;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;1:1;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
8;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE;;;;;;;;;;;;;;;;;1;
9;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;1:0;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
10;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;1:1;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée (%s).
11;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;1:1;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé par au moins un directeur.
12;1;\Application\Provider\Privilege\ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;1;
13;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;1:0;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
14;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
15;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE;;;;;;;;;;;;;;;;;1;
16;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;;;1:1;;;;;;;;;;0;La validation par la BU a été faite.
17;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_RDV_BU;;;;;;;;;;;;;;;;;1;
18;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;1:1;;;;;;;;;;0;La validation par la BU a été faite.
19;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;1:0;;;;;0;La BU n'a pas renseigné toutes informations requises.
20;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU;;;;;;;;;;;;;;;;;1;
21;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;;;;;;1:0;;;;;;;0;
22;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;1:1;;;;;2:1;;;;;;;0;
23;1;\Application\Provider\Privilege\ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR;;;;;1:0;;;;;2:1;;;;;;;1;
24;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;1:0;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse.
25;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
26;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;;1:1;;;;;;;;;;;;;;0;La date butoir pour le dépôt de la version corrigée est dépassée (%s).
27;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE;;1:1;3:0;2:0;;;;;;;;;;;;;1;
28;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;1:1;;;;;;;;;0;
29;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR;;;;;;;;;;;;;;;;;1;
30;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;1:0;;;;;;;;0;Le dépôt de la version corrigée n'a pas encore été validé par le doctorant.
31;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;1:0;;0;
32;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE;;;;;;;;;;;;;;;;;1;
33;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;1:0;0;
34;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR;;;;;;;;;;;;;;;;;1;
35;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;1:1;;;;;;0;
36;1;\Application\Provider\Privilege\ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE;;;;;;;;;;;;;;;;;1;
        ;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;1:1;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
37;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
38;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE;;;;;;;;;;;;;;;;;1;
39;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;1:0;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
40;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
41;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;1;
42;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;1:0;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
43;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;1:0;;;;;;;;;;;0;Le dépôt d'une version initiale doit être fait au préalable.
44;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;1:1;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
45;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;1;
46;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;1:1;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
47;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
48;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE;;;;;;;;;;;;;;;;;1;
49;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;1:0;;;;;;;;;;;;;;;0;Aucune correction n'est attendue pour cette thèse
50;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;2:1;;;;;;;;;;;;;0;Opération impossible dès lors que le dépôt de la version corrigée a été validé.
51;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE;;;;;;;;;;;;;;;;;1;
52;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;1:1;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée est attendue.
53;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;1:1;;;;;;;;;;0;Opération impossible dès lors que la BU a validé.
54;0;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;1:1;;;;;;;;;;;;;;;0;Opération impossible dès lors qu’une version corrigée a été déposée.
55;1;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE;;;;;;;;;;;;;;;;;1;
56;0;\Application\Provider\Privilege\ThesePrivileges::THESE_SAISIE_ATTESTATIONS /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;1;
57;1;\Application\Provider\Privilege\ThesePrivileges::THESE_TELECHARGEMENT_FICHIER /* évite UnexpectedPrivilegeException */;;;;;;;;;;;;;;;;;1;
EOT;
    }


}
