<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Form\Direction\DirectionForm;
use These\Form\Encadrement\EncadrementForm;
use These\Form\Generalites\GeneralitesForm;
use These\Form\Structures\StructuresForm;
use These\Form\TheseSaisie\TheseSaisieForm;
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
        $form = $this->getGeneralitesForm();
        $viewModel = new ViewModel([
            'form' => $form,
        ]);

        $form->bind($this->theseService->newThese());

        if (!$request->isPost()) {
            return $viewModel;
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $viewModel;
        }

        /** @var These $these */
        $these = $form->getData();
        $this->theseService->saveThese($these, $domaine);

        $this->flashMessenger()->addSuccessMessage("Thèse créée avec succès.");

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }

    public function generalitesAction()
    {
        return $this->modifier($this->getGeneralitesForm(), 'generalites');
    }

    public function directionAction()
    {
        return $this->modifier($this->getDirectionForm(), 'direction');
    }

    public function structuresAction()
    {
        return $this->modifier($this->getStructuresForm(), 'structures');
    }

    public function encadrementAction()
    {
        return $this->modifier($this->getEncadrementForm(), 'encadrement');
    }

    /**
     * Conservé pour mémoire suite aux conflits.
     */
    public function saisieAction()
    {
        $theseId = $this->params()->fromRoute('these');
        if ($theseId !== null) {
            $these = $this->requestedThese();
        } else {
            $these = new These();
            $these->setSource($this->source);
            $these->setSourceCode(uniqid());
        }

        $form = $this->getTheseSaisieForm();
        $domainesHal = $this->domaineHalService->getDomainesHalAsOptions();
        $form->get('domaineHal')->setDomainesHal($domainesHal);

        $form->bind($these);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            //Permet de gérer le cas où aucune sélection n'est effectuée (afin de passer dans l'hydrateur)
            if (!isset($data['domaineHal'])) {
                $data['domaineHal'] = array("domaineHal" => array(""));
            }
            $form->setData($data);

            if ($form->isValid()) {
                if ($theseId === null) {
                    $this->getTheseService()->create($these);
                } else {
                    $this->getTheseService()->update($these);
                }
                //ACTEUR //
                /** Gestion des acteurs */
                if ($data['directeur-individu']['id'] and $data['directeur-qualite'] and $data['directeur-etablissement']) {
                    /** @var Individu $individu */
                    $individu = $this->getIndividuService()->getRepository()->find($data['directeur-individu']['id']);
                    $qualite = $this->getQualiteService()->getQualite($data['directeur-qualite']);
                    /** @var Etablissement $etablissement */
                    $etablissement = $this->getEtablissementService()->getRepository()->find($data['directeur-etablissement']);
                    if ($individu and $qualite and $etablissement) {
                        $this->getActeurService()->creerOrModifierActeur($these, $individu, Role::CODE_DIRECTEUR_THESE, $qualite, $etablissement);
                    }
                }
                $temoins = [];
                for ($i = 1; $i <= TheseSaisieForm::NBCODIR; $i++) {
                    if ($data['codirecteur' . $i . '-individu']['id'] and $data['codirecteur' . $i . '-qualite'] and $data['codirecteur' . $i . '-etablissement']) {
                        /** @var Individu $individu */
                        $individu = $this->getIndividuService()->getRepository()->find($data['codirecteur' . $i . '-individu']['id']);
                        $qualite = $this->getQualiteService()->getQualite($data['codirecteur' . $i . '-qualite']);
                        /** @var Etablissement $etablissement */
                        $etablissement = $this->getEtablissementService()->getRepository()->find($data['codirecteur' . $i . '-etablissement']);
                        if ($individu and $qualite and $etablissement) {
                            $acteur = $this->getActeurService()->creerOrModifierActeur($these, $individu, Role::CODE_CODIRECTEUR_THESE, $qualite, $etablissement);
                            $temoins[] = $acteur->getId();
                        }
                    }
                }
                $codirecteurs = $this->getActeurService()->getRepository()->findActeursByTheseAndRole($these, Role::CODE_CODIRECTEUR_THESE);
                foreach ($codirecteurs as $codirecteur) {
                    if (array_search($codirecteur->getId(), $temoins) === false) $this->getActeurService()->historise($codirecteur);
                }


                return $this->redirect()->toRoute('these/saisie', ['these' => $these->getId()], [], true);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    private function modifier(Form $form, string $domaine)
    {
        $request = $this->getRequest();
        $these = $this->requestedThese();

        $viewModel = new ViewModel([
            'these' => $these,
            'form' => $form,
            'formPartial' => "these/these-saisie/partial/$domaine",
        ]);
        $viewModel->setTemplate('these/these-saisie/modifier');

        $form->bind($these);

        if (!$request->isPost()) {
            return $viewModel;
        }

        $form->setData($request->getPost());
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