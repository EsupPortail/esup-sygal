<?php

namespace ApplicationFunctionalTest\Service\Workflow;

use Application\Entity\Db\WfEtape;

class WorkflowServiceGeneralTest extends WorkflowServiceAbstractTest
{
    public function test_necessite_de_recalculer_le_workflow()
    {
        $etape1 = $this->wfs->getCurrent($this->these)->getEtape();

        /******************/
        $fichier = $this->ep()->fichierThese($this->these);
        $this->em()->flush($fichier);
        // NB: pas de reload du workflow
        /******************/

        // sans reload du workflow, ça n'avance pas !
        $etape2 = $this->wfs->getCurrent($this->these)->getEtape();
        $this->assertEquals($etape1->getCode(), $etape2->getCode());

        // après un reload, c'est mieux !
        $this->wfs->reloadWorkflow($this->these);
        $etape2 = $this->wfs->getCurrent($this->these)->getEtape();
        $this->assertNotEquals($etape1->getCode(), $etape2->getCode());
    }

    public function test_1ere_etape_attendue()
    {
        /************************************************************************
         * Etape attendue : WfEtape::CODE_DEPOT_VERSION_ORIGINALE
         ************************************************************************/

        $codeEtapeAttendue = WfEtape::CODE_DEPOT_VERSION_ORIGINALE;
        $this->assertEtapeEstCourante($codeEtapeAttendue);
        $this->assertEtapeEstFranchie($codeEtapeAttendue, false);
        $this->assertEtapeEstAtteignable($codeEtapeAttendue, true);
        $this->assertEtapesApresSontAtteignables($codeEtapeAttendue, false);
        $this->assertEtapesApresSontFranchies($codeEtapeAttendue, false);
    }
}
