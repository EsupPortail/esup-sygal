<?php

namespace Application\Controller;

use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuCompl;
use Application\Form\IndividuCompl\IndividuComplFormAwareTrait;
use Application\Service\IndividuCompl\IndividuComplServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndividuComplController extends AbstractController
{
    use EntityManagerAwareTrait;
    use IndividuComplServiceAwareTrait;
    use IndividuComplFormAwareTrait;

    public function indexAction() : ViewModel
    {
        $complements = $this->getIndividuComplService()->getComplements();

        return new ViewModel([
            'complements' => $complements,
        ]);
    }

    public function afficherAction() : ViewModel
    {
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->findRequestedIndividuCompl($this);

        return new ViewModel([
            'title' => "Affichage du complément d'individu",
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
                $this->getIndividuComplService()->create($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Ajout du complément d'individu effectué.");
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un complément d'individu",
            'form' => $form,
        ]);
        $vm->setTemplate('application/individu-compl/formulaire');
        return $vm;
    }

    public function gererAction() : ViewModel
    {
        $individu = $this->getEntityManager()->getRepository(Individu::class)->findRequestedIndividu($this);
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->findIndividuComplByIndividu($individu);

        if ($individuCompl === null) {
            $individuCompl = new IndividuCompl();
            $individuCompl->setIndividu($individu);
            $individuCompl = $this->getIndividuComplService()->create($individuCompl);
        }

        $form = $this->getIndividuComplForm();
        $form->setAttribute('action', $this->url()->fromRoute('individu-compl/gerer', ['individu' => $individu->getId()], [], true));
        $form->bind($individuCompl);
        $form->get('individu')->setAttribute('readonly','readonly');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getIndividuComplService()->update($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Gestion du complément pour [".$individu->getNomComplet()."] effectué.");
            }
        }

        $vm = new ViewModel([
            'title' => "Gestion du complément pour [".$individu->getNomComplet()."]",
            'form' => $form,
        ]);
        $vm->setTemplate('application/individu-compl/formulaire');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->findRequestedIndividuCompl($this);

        $form = $this->getIndividuComplForm();
        $form->setAttribute('action', $this->url()->fromRoute('individu-compl/modifier', ['individu-compl' => $individuCompl->getId()], [], true));
        $form->bind($individuCompl);
        $form->get('individu')->setAttribute('readonly','readonly');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getIndividuComplService()->update($individuCompl);
                $this->flashMessenger()->addSuccessMessage("Modification du complément d'individu effectuée.");
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'un complément d'individu",
            'form' => $form,
        ]);
        $vm->setTemplate('application/individu-compl/formulaire');
        return $vm;
    }

    public function historiserAction() : Response
    {
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->findRequestedIndividuCompl($this);
        $this->getIndividuComplService()->historise($individuCompl);
        $this->flashMessenger()->addSuccessMessage("Historisation du complément d'individu effectuée.");

        return $this->redirect()->toRoute('individu-compl', [], [], true);
    }

    public function restaurerAction() : Response
    {
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->findRequestedIndividuCompl($this);
        $this->getIndividuComplService()->restore($individuCompl);
        $this->flashMessenger()->addSuccessMessage("Restauration du complément d'individu effectuée.");

        return $this->redirect()->toRoute('individu-compl', [], [], true);
    }

    public function detruireAction() : ViewModel
    {
        $individuCompl = $this->getEntityManager()->getRepository(IndividuCompl::class)->findRequestedIndividuCompl($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getIndividuComplService()->delete($individuCompl);
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