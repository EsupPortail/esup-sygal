<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Financement;
use Application\Entity\Db\OrigineFinancement;
use Application\Entity\Db\Source;
use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Pays\PaysServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Fieldset\Financement\FinancementFieldset;
use These\Form\Direction\DirectionForm;
use These\Form\Encadrement\EncadrementForm;
use These\Form\Generalites\GeneralitesForm;
use These\Form\Structures\StructuresForm;
use These\Form\TheseFormsManagerAwareTrait;
use These\Form\TheseSaisie\TheseSaisieFormAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class TheseSaisieController extends AbstractController {
    use ActeurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseSaisieFormAwareTrait;
    use SourceAwareTrait;
    use DomaineHalServiceAwareTrait;
    use TheseServiceAwareTrait;
    use TheseFormsManagerAwareTrait;
    use FinancementServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use PaysServiceAwareTrait;

    /** FONCTIONS TEMPORAIRES A DEPLACER PLUS TARD */
    /**
     * @param These $these
     * @param Individu $individu
     * @param string $roleCode
     * @return string
     */
    public function generateCodeSourceActeur(These $these, Individu $individu, string $roleCode) : string
    {
        $code = $these->getId() . "_". $individu->getId() . "_" . $roleCode;
        return $code;
    }

    private ?GeneralitesForm $generalitesForm = null;
    private ?DirectionForm $directionForm = null;
    private ?StructuresForm $structuresForm = null;
    private ?EncadrementForm $encadrementForm = null;

    public function ajouterAction()
    {
        $request = $this->getRequest();
        $domaine = 'generalites';
//        $form = $this->getGeneralitesForm();
        $form = $this->getTheseSaisieForm();
        $domainesHal = $this->domaineHalService->getDomainesHalAsOptions();
        $form->get('generalites')->get('domaineHal')->setDomainesHal($domainesHal);
        $origines = $this->financementService->findOriginesFinancements("libelleLong");
        $pays = $this->paysService->getPaysAsOptions();
        $form->get('generalites')->get('titreAcces')->setPays($pays);

        $viewModel = new ViewModel([
            'form' => $form,
        ]);

        $form->bind($this->theseService->newThese());

        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        $form->setData($data);
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var These $these */
        $these = $form->getData();
        $these->setSource($this->source);
        $these->setSourceCode(uniqid());
        $this->theseService->saveThese($these, $domaine);

        $this->flashMessenger()->addSuccessMessage("Thèse créée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }

    public function indexAction()
    {
        return $this->modifier($this->getTheseSaisieForm(), 'index');
    }

//    public function directionAction()
//    {
//        return $this->modifier($this->getDirectionForm(), 'direction');
//    }
//
//    public function structuresAction()
//    {
//        return $this->modifier($this->getStructuresForm(), 'structures');
//    }
//
//    public function encadrementAction()
//    {
//        return $this->modifier($this->getEncadrementForm(), 'encadrement');
//    }

    public function modifier(Form $form, string $domaine)
    {
        $request = $this->getRequest();
        $these = $this->requestedThese();
        $domainesHal = $this->domaineHalService->getDomainesHalAsOptions();
        $form->get('generalites')->get('domaineHal')->setDomainesHal($domainesHal);
        $origines = $this->financementService->findOriginesFinancements("libelleLong");
        $pays = $this->paysService->getPaysAsOptions();
        $form->get('generalites')->get('titreAcces')->setPays($pays);

        /** @var FinancementFieldset $financement */
//        $financements = $form->get('financements');
//        $financements->setOrigineFinancementsPossibles($origines);

//        foreach ($financements as $financement) {
//            if ($financement instanceof FinancementFieldset) {
//                $financement->setOrigineFinancementsPossibles($origines);
//            }
//        }

        $viewModel = new ViewModel([
            'these' => $these,
            'form' => $form,
        ]);
        $viewModel->setTemplate('these/these-saisie/modifier');

        $form->bind($these);

        if (!$request->isPost()) {
            return $viewModel;
        }

        $data = $request->getPost();
        //Permet de gérer le cas où aucune sélection n'est effectuée (afin de passer dans l'hydrateur)
        if (!isset($data["generalites"]['domaineHal'])) {
            $generalites = $data->get('generalites', []);
            $generalites['domaineHal'] = ['domaineHal' => ['']];
            $data->set('generalites', $generalites);
        }

//        if (!isset($data["financements"]['origineFinancement'])) {
//            $financement = $data->get('financements', []);
//            $financement['origineFinancement'] = [''];
//            $data->set('financements', $financements);
//        }

        $form->setData($data);
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var These $these */
        $these = $form->getData();
        $this->theseService->saveThese($these, $domaine);

        $this->flashMessenger()->addSuccessMessage("Thèse modifiée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], ['fragment' => $domaine], true);
    }

    public function getGeneralitesForm(): GeneralitesForm
    {
        if ($this->generalitesForm === null) {
            $this->generalitesForm = $this->theseFormsManager->get(GeneralitesForm::class);
        }

        return $this->generalitesForm;
    }

    public function getDirectionForm(): DirectionForm
    {
        if ($this->directionForm === null) {
            $this->directionForm = $this->theseFormsManager->get(DirectionForm::class);
        }

        return $this->directionForm;
    }

    public function getStructuresForm(): StructuresForm
    {
        if ($this->structuresForm === null) {
            $this->structuresForm = $this->theseFormsManager->get(StructuresForm::class);
        }

        return $this->structuresForm;
    }

    public function getEncadrementForm(): EncadrementForm
    {
        if ($this->encadrementForm === null) {
            $this->encadrementForm = $this->theseFormsManager->get(EncadrementForm::class);
        }

        return $this->encadrementForm;
    }
}