<?php

namespace ApplicationFunctionalTest\Service\Workflow\VersionInitiale;

use Depot\Entity\Db\WfEtape;
use ApplicationFunctionalTest\Service\Workflow\WorkflowServiceAbstractTest;

/**
 * Cas du dépôt d'une VERSION ORIGINALE "INITIALE" (i.e. sans correction).
 *
 * @author Unicaen
 */
class WorkflowServiceTest extends WorkflowServiceAbstractTest
{
    /**
     * Cas de figure :
     * - version originale VALIDE.
     */
    public function test_etapes_chemin_1()
    {
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_ORIGINALE);

        $this->_franchir_etape_depot_version_originale();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ATTESTATIONS);

        $this->_franchir_etape_attestations();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_AUTORISATION_DIFFUSION_THESE);

        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_SIGNALEMENT_THESE);

        $this->_franchir_etape_signalement_these();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE);

        $this->_franchir_etape_archivabilite_version_originale(true); // version originale VALIDE.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT);

        $this->_franchir_etape_rdv_bu_saisie_doctorant(); // insertion d'un RDV BU avec infos doctorant.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_VALIDATION_BU);

        //  - remplissage du RDV BU avec les infos BU,
        //  - insertion d'une validation.
        $this->_franchir_etape_rdv_bu_validation_bu();
        $this->_assertSeuleEtapeCourante(WfEtape::PSEUDO_ETAPE_FINALE); // pseudo-étape située après la dernière étape
    }

    /**
     * Cas de figure :
     * - version originale NON VALIDE.
     * - version d'archivage VALIDE.
     * - vérification avec réponse POSITIVE (conformité).
     */
    public function test_etapes_chemin_2()
    {
        $this->_franchir_etape_depot_version_originale();
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();

        $this->_franchir_etape_archivabilite_version_originale(false); // version originale VALIDE.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_ARCHIVAGE);

        $this->_franchir_etape_depot_version_archivage();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE);

        $this->_franchir_etape_archivabilite_version_archivage(true); // version d'archivage VALIDE
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_VERIFICATION_VERSION_ARCHIVAGE);

        $this->_franchir_etape_verification_version_archivage(true); // vérification avec réponse POSITIVE (conformité).
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT);

        $this->_franchir_etape_rdv_bu_saisie_doctorant(); // insertion d'un RDV BU avec infos doctorant.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_VALIDATION_BU);

        //  - remplissage du RDV BU avec les infos BU,
        //  - insertion d'une validation.
        $this->_franchir_etape_rdv_bu_validation_bu();
        $this->_assertSeuleEtapeCourante(WfEtape::PSEUDO_ETAPE_FINALE); // pseudo-étape située après la dernière étape
    }

    /**
     * Cas de figure :
     * - version originale NON VALIDE.
     * - version d'archivage VALIDE.
     * - vérification avec réponse NEGATIVE (non conformité).
     */
    public function test_etapes_chemin_3()
    {
        $this->_franchir_etape_depot_version_originale();
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();

        $this->_franchir_etape_archivabilite_version_originale(false); // version originale VALIDE.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_ARCHIVAGE);

        $this->_franchir_etape_depot_version_archivage();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE);

        $this->_franchir_etape_archivabilite_version_archivage(true); // version d'archivage VALIDE
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_VERIFICATION_VERSION_ARCHIVAGE);

        //  - vérification avec réponse NEGATIVE (non conformité).
        $this->_franchir_etape_verification_version_archivage(false);
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT);

        //  - insertion d'un RDV BU avec infos doctorant.
        $this->_franchir_etape_rdv_bu_saisie_doctorant();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_VALIDATION_BU);

        //  - remplissage du RDV BU avec les infos BU,
        //  - insertion d'une validation.
        $this->_franchir_etape_rdv_bu_validation_bu();
        $this->_assertSeuleEtapeCourante(WfEtape::PSEUDO_ETAPE_FINALE); // pseudo-étape située après la dernière étape
    }

    /**
     * Cas de figure :
     * - version originale NON VALIDE.
     * - version d'archivage NON VALIDE.
     */
    public function test_etapes_chemin_4()
    {
        $this->_franchir_etape_depot_version_originale();
        $this->_franchir_etape_attestations();
        $this->_franchir_etape_autorisation_diffusion_these();
        $this->_franchir_etape_signalement_these();

        $this->_franchir_etape_archivabilite_version_originale(false); // version originale VALIDE.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_DEPOT_VERSION_ARCHIVAGE);

        $this->_franchir_etape_depot_version_archivage();
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_ARCHIVABILITE_VERSION_ARCHIVAGE);

        $this->_franchir_etape_archivabilite_version_archivage(false); // version d'archivage NON VALIDE
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT);

        $this->_franchir_etape_rdv_bu_saisie_doctorant(); // insertion d'un RDV BU avec infos doctorant.
        $this->_assertSeuleEtapeCourante(WfEtape::CODE_RDV_BU_VALIDATION_BU);

        //  - remplissage du RDV BU avec les infos BU,
        //  - insertion d'une validation.
        $this->_franchir_etape_rdv_bu_validation_bu();
        $this->_assertSeuleEtapeCourante(WfEtape::PSEUDO_ETAPE_FINALE); // pseudo-étape située après la dernière étape
    }
}
