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

    public function ajouterAction()
    {
        $individu = $this->individuService->getRepository()->findRequestedIndividu($this);
        $individuEmail = $individu->getComplement() === null ? $individu->getEmailPro() : null;

        $individuCompl = new IndividuCompl();
        $individuCompl->setIndividu($individu);

        $this->individuComplForm->setAttribute('action', $this->url()->fromRoute('individu-compl/ajouter', [], [], true));
        $this->individuComplForm->bind($individuCompl);
        $this->individuComplForm->get('individu')->setAttribute('readonly','readonly');
        $this->individuComplForm->get('individuEmail')->setValue($individuEmail)->setAttribute('readonly','readonly');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->individuComplForm->setData($data);
            if ($this->individuComplForm->isValid()) {
                $this->individuComplService->create($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Ajout du complément d'individu effectué.");

                if ($request->isXmlHttpRequest()) {
                    exit();
                }
                return $this->redirect()->toRoute('individu/voir', ['individu' => $individu->getId()]);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un complément d'individu",
            'individuCompl' => $individuCompl,
            'form' => $this->individuComplForm,
        ]);
        $vm->setTemplate('individu/individu-compl/formulaire');

        return $vm;
    }

    public function modifierAction() : ViewModel|Response
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);
        $individu =$individuCompl->getIndividu();
        $individuEmail = $individu->getEmailPro(false);

        $this->individuComplForm->setAttribute('action', $this->url()->fromRoute('individu-compl/modifier', ['individu-compl' => $individuCompl->getId()], [], true));
        $this->individuComplForm->bind($individuCompl);
        $this->individuComplForm->get('individu')->setAttribute('readonly','readonly');
        $this->individuComplForm->get('individuEmail')->setValue($individuEmail)->setAttribute('readonly','readonly');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->individuComplForm->setData($data);
            if ($this->individuComplForm->isValid()) {
                $this->individuComplService->update($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Modification du complément d'individu effectuée.");

                if ($request->isXmlHttpRequest()) {
                    exit();
                }
                return $this->redirect()->toRoute('individu/voir', ['individu' => $individuCompl->getIndividu()->getId()]);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'un complément d'individu",
            'individuCompl' => $individuCompl,
            'form' => $this->individuComplForm,
        ]);
        $vm->setTemplate('individu/individu-compl/formulaire');

        return $vm;
    }

    public function historiserAction() : Response
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);
        $this->individuComplService->historise($individuCompl);
        $this->flashMessenger()->addSuccessMessage("Historisation du complément d'individu effectuée.");

        return $this->redirect()->toRoute('individu/voir', ['individu' => $individuCompl->getIndividu()->getId()]);
    }

    public function restaurerAction() : Response
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);
        $this->individuComplService->restore($individuCompl);
        $this->flashMessenger()->addSuccessMessage("Restauration du complément d'individu effectuée.");

        return $this->redirect()->toRoute('individu/voir', ['individu' => $individuCompl->getIndividu()->getId()]);
    }

    public function detruireAction(): void
    {
        $individuCompl = $this->individuComplService->findRequestedIndividuCompl($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->individuComplService->delete($individuCompl);
            $this->flashMessenger()->addSuccessMessage("Suppression du complément d'individu effectuée.");

            exit();
        }
    }
}