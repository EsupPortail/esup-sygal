<?php

namespace ApplicationFunctionalTest\Service\Workflow\VersionCorrigee;

use These\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\WfEtape;
use ApplicationFunctionalTest\Service\Workflow\WorkflowServiceAbstractTest;

/**
 * Cas du dépôt d'une VERSION ORIGINALE CORRIGÉE.
 *
 * @author Unicaen
 */
class WorkflowServiceTest extends WorkflowServiceAbstractTest
{
    public function test_deposer_version_corrigee_sans_version_initiale_est_bloquant()
    {
        // dépôt version corrigée alors qu'aucune version originale n'a été déposée
        $this->_deposer_fichier_version_originale(true);
        $this->wfs->reloadWorkflow($this->these);

        $codeEtapeSuivante = WfEtape::CODE_ATTESTATIONS;
        $this->assertEtapesAvantSontFranchies($codeEtapeSuivante, false);
        $this->assertEtapesAvantSontAtteignables($codeEtapeSuivante, true);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE);
        $this->assertEtapeEstAtteignable($codeEtapeSuivante, false);
        $this->assertEtapesApresSontFranchies($codeEtapeSuivante, false);
        $this->assertEtapesApresSontAtteignables($codeEtapeSuivante, false);
    }

    public function test_necessite_temoin_correction_autorisee()
    {
        // correction autorisee = null
        $this->these->setCorrectionAutorisee(null);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE);

        // correction autorisee = mineure
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE);

        // correction autorisee = majeure
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_OBLIGATOIRE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE);

        $this->_franchir_etape_rdv_bu_validation_bu();

        // correction autorisee = mineure
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE);

        // correction autorisee = majeure
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_OBLIGATOIRE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE);
    }

    public function test_etape_validation_correction_directeurs_necessite_toutes_les_validations()
    {
        // pré-requis: correction autorisee != null
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE);

        // franchissement des étapes avant CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR
        $this->_franchir_etape_depot_version_originale($corrigee = true);
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();
        $this->_franchir_etape_archivabilite_version_originale(true, true);
        $this->_franchir_etape_validation_depot_version_corrigee_doctorant();

        // 2 directeurs ==> 2 validations requises
        $individu1 = $this->ep()->individu();
        $individu2 = $this->ep()->individu();
        $directeur1 = $this->ep()->directeurThese($this->these, $individu1);
        $directeur2 = $this->ep()->directeurThese($this->these, $individu2);
        $this->em()->flush($individu1);
        $this->em()->flush($individu2);
        $this->em()->flush($directeur1);
        $this->em()->flush($directeur2);

        $v1 = $this->ep()->validation($this->these,TypeValidation::CODE_CORRECTION_THESE, $directeur1->getIndividu());
        $this->em()->flush($v1);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstCourante(WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR);

        $v2 = $this->ep()->validation($this->these,TypeValidation::CODE_CORRECTION_THESE, $directeur2->getIndividu());
        $this->em()->flush($v2);
        $this->wfs->reloadWorkflow($this->these);
        $this->assertEtapeEstFranchie(WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR);
    }

    /**
     * Cas de figure :
     * - version originale corrigée VALIDE.
     */
    public function test_cas_version_originale_valide()
    {
        // pré-requis: correction autorisee != null
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE);

        $this->_franchir_etape_depot_version_originale($corrigee = true);
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ATTESTATIONS);

        $this->_franchir_etape_attestations();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_AUTORISATION_DIFFUSION_THESE);

        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_SIGNALEMENT_THESE);

        $this->_franchir_etape_signalement_these();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE);

        $this->_franchir_etape_archivabilite_version_originale(true, true); // version originale VALIDE.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT);

        $this->_franchir_etape_validation_depot_version_corrigee_doctorant();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR);

        // 2 directeurs ==> 2 validations requises
        $individu1 = $this->ep()->individu();
        $individu2 = $this->ep()->individu();
        $directeur1 = $this->ep()->directeurThese($this->these, $individu1);
        $directeur2 = $this->ep()->directeurThese($this->these, $individu2);
        $this->em()->flush($individu1);
        $this->em()->flush($individu2);
        $this->em()->flush($directeur1);
        $this->em()->flush($directeur2);

        $this->_franchir_etape_validation_depot_version_corrigee_directeurs();
        $this->_assertSeuleEtapeCourante(WfEtape::PSEUDO_ETAPE_FINALE);
    }

    /**
     * Cas de figure :
     * - version originale corrigée NON VALIDE.
     * - version d'archivage corrigée VALIDE.
     * - vérification avec réponse POSITIVE (conformité).
     */
    public function test_cas_vo_nonvalide_va_valide_conforme()
    {
        // pré-requis: correction autorisee != null
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);

        $this->_franchir_etape_depot_version_originale($corrigee = true);
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();
        $this->_franchir_etape_archivabilite_version_originale(false, true); // version originale NON VALIDE
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_ARCHIVAGE_CORRIGEE);

        $this->_franchir_etape_depot_version_archivage(true);
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE);

        $this->_franchir_etape_archivabilite_version_archivage(true, true); // version d'archivage VALIDE
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE);

        $this->_franchir_etape_verification_version_archivage(true, true); // vérification avec réponse POSITIVE (conformité).
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT);

        $this->_franchir_etape_validation_depot_version_corrigee_doctorant();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR);

        // 2 directeurs ==> 2 validations requises
        $individu1 = $this->ep()->individu();
        $individu2 = $this->ep()->individu();
        $directeur1 = $this->ep()->directeurThese($this->these, $individu1);
        $directeur2 = $this->ep()->directeurThese($this->these, $individu2);
        $this->em()->flush($individu1);
        $this->em()->flush($individu2);
        $this->em()->flush($directeur1);
        $this->em()->flush($directeur2);

        $this->_franchir_etape_validation_depot_version_corrigee_directeurs();
        $this->_assertSeuleEtapeCourante(WfEtape::PSEUDO_ETAPE_FINALE);
    }

    /**
     * Cas de figure :
     * - version originale NON VALIDE.
     * - version d'archivage VALIDE.
     * - vérification avec réponse NEGATIVE (non conformité).
     */
    public function test_cas_vo_nonvalide_va_valide_nonconforme()
    {
        // pré-requis: correction autorisee != null
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);

        $this->_franchir_etape_depot_version_originale($corrigee = true);
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();
        $this->_franchir_etape_archivabilite_version_originale(false, true); // version originale NON VALIDE
        $this->_franchir_etape_depot_version_archivage(true);
        $this->_franchir_etape_archivabilite_version_archivage(true, true); // version d'archivage VALIDE
        $this->_franchir_etape_verification_version_archivage(false, true); // vérification avec réponse POSITIVE (conformité).

        // on reste à la même étape car la non conformité est bloquante
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE);
    }

    /**
     * Cas de figure :
     * - version originale NON VALIDE.
     * - version d'archivage NON VALIDE.
     */
    public function test_cas_vo_nonvalide_va_nonvalide()
    {
        // pré-requis: correction autorisee != null
        $this->these->setCorrectionAutorisee(These::CORRECTION_AUTORISEE_FACULTATIVE);
        $this->em()->flush($this->these);
        $this->wfs->reloadWorkflow($this->these);

        $this->_franchir_etape_depot_version_originale($corrigee = true);
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();
        $this->_franchir_etape_archivabilite_version_originale(false, true); // version originale NON VALIDE
        $this->_franchir_etape_depot_version_archivage(true);
        $this->_franchir_etape_archivabilite_version_archivage(false, true); // version d'archivage NON VALIDE

        // on reste à la même étape car la non archivabilité est bloquante
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE);
    }

}
