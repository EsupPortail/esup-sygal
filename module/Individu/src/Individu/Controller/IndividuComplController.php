<?php

namespace Individu\Controller;

use Application\Controller\AbstractController;
use Individu\Entity\Db\IndividuCompl;
use Individu\Form\IndividuCompl\IndividuComplFormAwareTrait;
use Individu\Service\IndividuCompl\IndividuComplServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

class IndividuComplController extends AbstractController
{
    use IndividuServiceAwareTrait;
    use IndividuComplServiceAwareTrait;

    use IndividuComplFormAwareTrait;

    public function indexAction() : ViewModel
    {
        $complements = $this->individuComplService->findComplements();

        return new ViewModel([
            'complements' => $complements,
        ]);
    }

    public function afficherAction() : ViewModel
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);

        return new ViewModel([
            'title' => "Détails d'un complément d'individu",
            'complement' => $individuCompl,
        ]);
    }

    public function ajouterAction() : ViewModel
    {
        $individuCompl = new IndividuCompl();

        $form = $this->getIndividuComplForm();
        $form->setAttribute('action', $this->url()->fromRoute('individu-compl/ajouter', [], [], true));
        $form->bind($individuCompl);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->individuComplService->create($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Ajout du complément d'individu effectué.");
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un complément d'individu",
            'form' => $form,
        ]);
        $vm->setTemplate('individu/individu-compl/formulaire');
        return $vm;
    }

    public function gererAction() : ViewModel
    {
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);
        $individuCompl = $this->individuComplService->getRepository()->findIndividuComplByIndividu($individu);

        if ($individuCompl === null) {
            $individuCompl = new IndividuCompl();
            $individuCompl->setIndividu($individu);
            $individuCompl = $this->individuComplService->create($individuCompl);
        }

        $form = $this->individuComplForm;
        $form->setAttribute('action', $this->url()->fromRoute('individu-compl/gerer', ['individu' => $individu->getId()], [], true));
        $form->bind($individuCompl);
        $form->get('individu')->setAttribute('readonly','readonly');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->individuComplService->update($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Gestion du complément pour [".$individu->getNomComplet()."] effectué.");
            }
        }

        $vm = new ViewModel([
            'title' => "Gestion du complément pour [".$individu->getNomComplet()."]",
            'form' => $form,
        ]);
        $vm->setTemplate('individu/individu-compl/formulaire');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);

        $form = $this->getIndividuComplForm();
        $form->setAttribute('action', $this->url()->fromRoute('individu-compl/modifier', ['individu-compl' => $individuCompl->getId()], [], true));
        $form->bind($individuCompl);
        $form->get('individu')->setAttribute('readonly','readonly');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->individuComplService->update($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Modification du complément d'individu effectuée.");
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'un complément d'individu",
            'form' => $form,
        ]);
        $vm->setTemplate('individu/individu-compl/formulaire');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);
        $this->individuComplService->historise($individuCompl);
        $this->flashMessenger()->addSuccessMessage("Historisation du complément d'individu effectuée.");

        return $this->redirect()->toRoute('individu-compl', [], [], true);
    }

    public function restaurerAction() : Response
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);
        $this->individuComplService->restore($individuCompl);
        $this->flashMessenger()->addSuccessMessage("Restauration du complément d'individu effectuée.");

        return $this->redirect()->toRoute('individu-compl', [], [], true);
    }

    public function detruireAction() : ViewModel
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->individuComplService->delete($individuCompl);
            $this->flashMessenger()->addSuccessMessage("Destruction du complément d'individu effectuée.");
            exit();
        }

        $vm = new ViewModel();
        if ($individuCompl !== null) {
            $vm->setVariables([
                'title' => "Suppression du complément",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('individu-compl/detruire', ["individu-compl" => $individuCompl->getId()], [], true),
            ]);
        }
        return $vm;
    }
}