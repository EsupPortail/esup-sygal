<?php

namespace Formation\Controller;

use Application\Controller\AbstractController;
use Formation\Service\Module\ModuleServiceAwareTrait;
use Laminas\Http\Response;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Formation\Entity\Db\Formation;
use Formation\Form\Formation\FormationFormAwareTrait;
use Formation\Service\Formation\FormationServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\View\Model\ViewModel;

class FormationController extends AbstractController
{
    use EntityManagerAwareTrait;
    use FormationServiceAwareTrait;
    use ModuleServiceAwareTrait;
    use FormationFormAwareTrait;

    use EtablissementServiceAwareTrait;

    public function indexAction() : ViewModel
    {
        /** Recupération des paramètres du filtre */
        $filtres = [
            'site' => $this->params()->fromQuery('site'),
            'libelle' => $this->params()->fromQuery('libelle'),
            'responsable' => $this->params()->fromQuery('responsable'),
            'modalite' => $this->params()->fromQuery('modalite'),
            'structure' => $this->params()->fromQuery('structure'),
        ];
        /** Listes pour les filtres */
        $listings = [
            'sites' => $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions(),
            'responsables' => $this->getFormationService()->getRepository()->fetchListeResponsable(),
            'structures' => $this->getFormationService()->getRepository()->fetchListeStructures(),
        ];

        $formations = $this->getFormationService()->getRepository()->fetchFormationsWithFiltres($filtres);

        return new ViewModel([
            'formations' => $formations,
            'filtres' => $filtres,
            'listings' => $listings,
        ]);
    }

    public function afficherAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        return new ViewModel([
            'formation' => $formation,
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $module = $this->getModuleService()->getRepository()->getRequestedModule($this);
        $formation = new Formation();

        if ($module !== null) $formation->setModule($module);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/ajouter', ['module' => ($module)?$module->getId():null], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->create($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $form = $this->getFormationForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/formation/modifier', [], [], true));
        $form->bind($formation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getFormationService()->update($formation);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une formation",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/formation/modifier');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $this->getFormationService()->historise($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/formation');

    }

    public function restaurerAction() : Response
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);
        $this->getFormationService()->restore($formation);

        $retour = $this->params()->fromQuery('retour');
        if ($retour !== null) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/formation');
    }

    public function supprimerAction()
    {
        $formation = $this->getFormationService()->getRepository()->getRequestedFormation($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getFormationService()->delete($formation);
            exit();
        }

        $vm = new ViewModel();
        if ($formation !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression d'une formation #" . $formation->getId(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/formation/supprimer', ["formation" => $formation->getId()], [], true),
            ]);
        }
        return $vm;
    }
}