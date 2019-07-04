<?php

namespace ApplicationFunctionalTest\Service\Workflow;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\FichierThese;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\VersionFichier;
use Application\Service\Workflow\WorkflowService;
use ApplicationUnitTest\Controller\AbstractControllerTestCase;

abstract class WorkflowServiceAbstractTest extends AbstractControllerTestCase
{
    protected $debug = false;

    /**
     * @var These
     */
    protected $these;

    /**
     * @var WorkflowService
     */
    protected $wfs;


    public function setUp()
    {
        parent::setUp();

        $this->wfs = $this->getWorkflowService();

        $this->these = $this->ep()->these();
        $this->em()->flush($this->these);
    }

    public function tearDown()
    {
        $this->ep()->removeNewEntities();
    }



    /**
     * @param string|null $codeEtape
     */
    protected function assertEtapeEstCourante($codeEtape)
    {
        $current = $this->wfs->getCurrent($this->these);

        if ($codeEtape !== null) {
            $this->assertEquals(
                $codeEtape,
                $current->getEtape()->getCode(),
                "L'étape courante devrait être '$codeEtape', et non '{$current->getEtape()->getCode()}'"
            );
        }
        else {
            $this->assertNull(
                $current,
                "L'étape courante devrait être celle située après la dernière étape"
            );
        }
    }

    /**
     * @param string $codeEtape
     * @param bool   $franchie
     */
    protected function assertEtapeEstFranchie($codeEtape, $franchie = true)
    {
        $testAll = false;
        if ($codeEtape === null) {
            $testAll = true;
            $franchie = !$franchie;
        }

        $wf = $this->wfs->getWorkflow($this->these);

        foreach ($wf as $r) {
            $etape = $r->getEtape();
            if ($testAll || $etape->getCode() === $codeEtape) {
                $this->assertEquals(
                    $etape->getFranchie(),
                    $franchie,
                    "Le témoin franchie de l'étape '{$etape->getCode()}' devrait être à " . ($franchie ? 'true' : 'false'));
            }
        }
    }

    /**
     * @param string $codeEtape
     * @param bool   $franchie
     */
    protected function assertEtapesAvantSontFranchies($codeEtape, $franchie)
    {
        foreach ($this->wfs->getBefore($this->these, $codeEtape) as $r) {
            $etape = $r->getEtape();
            $this->assertEquals(
                $franchie,
                $etape->getFranchie(),
                "Le témoin franchie de l'étape '{$etape->getCode()}' devrait être à " . ($franchie ? 'true' : 'false'));
            if ($this->debug) {
                fwrite(STDERR, PHP_EOL . "Etape avant '$codeEtape' : " . sprintf("%-45s", "'{$etape->getCode()}' : "). ($franchie ? 'franchie' : 'non franchie'));
            }
        }
        if ($this->debug) {
            fwrite(STDERR, PHP_EOL);
        }
    }

    /**
     * @param string $codeEtape
     * @param bool   $atteignable
     */
    protected function assertEtapesAvantSontAtteignables($codeEtape, $atteignable)
    {
        foreach ($this->wfs->getBefore($this->these, $codeEtape) as $r) {
            $etape = $r->getEtape();
            $this->assertEquals(
                $atteignable,
                $etape->getAtteignable(),
                "Le témoin atteignable de l'étape '{$etape->getCode()}' devrait être à " . ($atteignable ? 'true' : 'false'));
            if ($this->debug) {
                fwrite(STDERR, PHP_EOL . "Etape avant '$codeEtape' : " . sprintf("%-45s", "'{$etape->getCode()}' : ") . ($atteignable ? 'atteignable' : 'non atteignable'));
            }
        }
        if ($this->debug) {
            fwrite(STDERR, PHP_EOL);
        }
    }

    /**
     * @param string $codeEtape
     * @param bool   $franchie
     */
    protected function assertEtapesApresSontFranchies($codeEtape, $franchie)
    {
        foreach ($this->wfs->getAfter($this->these, $codeEtape) as $r) {
            $etape = $r->getEtape();
            $this->assertEquals(
                $franchie,
                $etape->getFranchie(),
                "Le témoin franchie de l'étape '{$etape->getCode()}' devrait être à " . ($franchie ? 'true' : 'false'));
            if ($this->debug) {
                fwrite(STDERR, PHP_EOL . "Etape après '$codeEtape' : " . sprintf("%-45s", "'{$etape->getCode()}' : ") . ($franchie ? 'franchie' : 'non franchie'));
            }
        }
        if ($this->debug) {
            fwrite(STDERR, PHP_EOL);
        }
    }

    /**
     * @param string $codeEtape
     * @param bool   $atteignable
     */
    protected function assertEtapesApresSontAtteignables($codeEtape, $atteignable)
    {
        foreach ($this->wfs->getAfter($this->these, $codeEtape) as $r) {
            $etape = $r->getEtape();
            $this->assertEquals(
                $atteignable,
                $etape->getAtteignable(),
                "Le témoin atteignable de l'étape '{$etape->getCode()}' devrait être à " . ($atteignable ? 'true' : 'false'));
            if ($this->debug) {
                fwrite(STDERR, PHP_EOL . "Etape après '$codeEtape' : " . sprintf("%-45s", "'{$etape->getCode()}' : ") . ($atteignable ? 'atteignable' : 'non atteignable'));
            }
        }
        if ($this->debug) {
            fwrite(STDERR, PHP_EOL);
        }
    }

    /**
     * @param string $codeEtape
     * @param bool   $atteignable
     */
    protected function assertEtapeEstAtteignable($codeEtape, $atteignable)
    {
        $testAll = false;
        if ($codeEtape === null) {
            $testAll = true;
            $atteignable = !$atteignable;
        }

        $wf = $this->wfs->getWorkflow($this->these);

        foreach ($wf as $r) {
            $etape = $r->getEtape();
            if ($testAll || $etape->getCode() === $codeEtape) {
                $this->assertEquals(
                    $etape->getAtteignable(),
                    $atteignable,
                    "Le témoin atteignable de l'étape '{$etape->getCode()}' devrait être à " . ($atteignable ? 'true' : 'false'));
            }
        }
    }


    protected function _assertSeuleEtapeCourante($codeEtape)
    {
        $this->assertEtapesAvantSontFranchies($codeEtape, true);
        $this->assertEtapesAvantSontAtteignables($codeEtape, true);
        $this->assertEtapeEstCourante($codeEtape);
        $this->assertEtapeEstFranchie($codeEtape, false);
        $this->assertEtapeEstAtteignable($codeEtape, true);
        $this->assertEtapesApresSontFranchies($codeEtape, false);
        $this->assertEtapesApresSontAtteignables($codeEtape, false);
    }




    protected function _deposer_fichier_version_originale($corrigee = false)
    {
        $version = $corrigee ? VersionFichier::CODE_ORIG_CORR : VersionFichier::CODE_ORIG;
        $fichier = $this->ep()->fichierThese($this->these, NatureFichier::CODE_THESE_PDF, $version);
        $this->em()->flush($fichier);
    }






    protected function _franchir_etape_depot_version_originale($corrigee = false)
    {
        // une version corrigée ne peut exister seule
        if ($corrigee) {
            $this->_deposer_fichier_version_originale(false);
        }

        $this->_deposer_fichier_version_originale($corrigee);

        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_attestations()
    {
        // Seul franchissement possible :
        //  - insertion d'une Attestation
        $attestation = $this->ep()->attestation($this->these);
        $this->em()->flush($attestation);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_autorisation_diffusion_these()
    {
        // Seul franchissement possible :
        //  - insertion d'une autorisation de diffusion
        $diffusion = $this->ep()->diffusion($this->these);
        $this->em()->flush($diffusion);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_signalement_these()
    {
        // Seul franchissement possible :
        //  - insertion d'un signalement
        $signalement = $this->ep()->signalement($this->these);
        $this->em()->flush($signalement);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_archivabilite_version_originale($estValide = true, $corrigee = false)
    {
        $version = $corrigee ? VersionFichier::CODE_ORIG_CORR : VersionFichier::CODE_ORIG;

        /** @var Fichier $fichier */
        $fichier = $this->these->getFichiersByNatureEtVersion(NatureFichier::CODE_THESE_PDF, $version)->first();
        // insertion de la validité si besoin
        $validiteFichier = $fichier->getValidite();
        if ($validiteFichier === null) {
            $validiteFichier = $this->ep()->validiteFichier($fichier, $estValide);
        }

        // Franchissement
        $validiteFichier->setEstValide($estValide);
        $this->em()->flush($validiteFichier);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_depot_version_archivage($corrigee = false)
    {
        $version = $corrigee ? VersionFichier::CODE_ARCHI_CORR : VersionFichier::CODE_ARCHI;

        // Seul franchissement possible :
        //  - insertion d'un fichier (version d'archivage)
        $fichier = $this->ep()->fichierThese($this->these, NatureFichier::CODE_THESE_PDF, $version);
        $this->em()->flush($fichier);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_archivabilite_version_archivage($estValide = true, $corrigee = false)
    {
        $version = $corrigee ? VersionFichier::CODE_ARCHI_CORR : VersionFichier::CODE_ARCHI;

        /** @var Fichier $fichier */
        $fichier = $this->these->getFichiersByNatureEtVersion(NatureFichier::CODE_THESE_PDF, $version, false)->first();
        // insertion de la validité si besoin
        $validiteFichier = $fichier->getValidite();
        if ($validiteFichier === null) {
            $validiteFichier = $this->ep()->validiteFichier($fichier, $estValide);
        }

        // Franchissement
        $validiteFichier->setEstValide($estValide);
        $this->em()->flush($validiteFichier);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_verification_version_archivage($estConforme = true, $corrigee = false)
    {
        $version = $corrigee ? VersionFichier::CODE_ARCHI_CORR : VersionFichier::CODE_ARCHI;

        /** @var FichierThese $fichier */
        $fichier = $this->these->getFichiersByNatureEtVersion(NatureFichier::CODE_THESE_PDF, $version, false)->first();

        // Franchissement
        $fichier->setEstConforme((int) $estConforme);
        $this->em()->flush($fichier);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_rdv_bu_saisie_doctorant()
    {
        $rdvBu = $this->ep()->rdvBu($this->these);
        $rdvBu
            ->setCoordDoctorant("06 06 06 06 06")
            ->setDispoDoctorant("Nuit");
        $this->em()->flush($rdvBu);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_rdv_bu_validation_bu()
    {
        $rdvBu = $this->these->getRdvBu();
        $rdvBu
            ->setConventionMelSignee(true)
            ->setExemplPapierFourni(true)
            ->setMotsClesRameau("mot clé")
            ->setVersionArchivableFournie(true);
        $validation = $this->ep()->validation($this->these, TypeValidation::CODE_RDV_BU);
        $this->em()->flush($rdvBu);
        $this->em()->flush($validation);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_validation_depot_version_corrigee_doctorant()
    {
        $validation = $this->ep()->validation(
            $this->these, TypeValidation::CODE_DEPOT_THESE_CORRIGEE);
        $this->em()->flush($validation);
        $this->wfs->reloadWorkflow($this->these);
    }

    protected function _franchir_etape_validation_depot_version_corrigee_directeurs()
    {
        $directeurs = $this->these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);

        // création d'une validation par directeur
        $validations = [];
        foreach ($directeurs as $directeur) {
            $validations[] = $this->ep()->validation(
                $this->these,
                TypeValidation::CODE_CORRECTION_THESE,
                $directeur->getIndividu());
        }

        $this->em()->flush($validations);
        $this->wfs->reloadWorkflow($this->these);

        return $validations;
    }
    

    /**
     * @return WorkflowService
     */
    protected function getWorkflowService()
    {
        /** @var WorkflowService $service */
        $service = $this->getApplicationServiceLocator()->get('WorkflowService');

        return $service;
    }
}
