<?php

namespace ComiteSuiviIndividuel\Controller;

use Application\Controller\AbstractController;
use ComiteSuiviIndividuel\Entity\Db\Membre;
use ComiteSuiviIndividuel\Form\Membre\MembreFromAwareTrait;
use ComiteSuiviIndividuel\Service\Membre\MembreServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class MembreController extends AbstractController
{
    use MembreServiceAwareTrait;
    use MembreFromAwareTrait;

    public function ajouterAction() : ViewModel
    {
        $these = $this->requestedThese();
        $membre = new Membre();

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi-individuel/membre/ajouter', ['these' => $these->getId()], [], true));
        $form->bind($membre);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $membre->setThese($these);
                $this->getMembreService()->create($membre);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'un membre de comité de suivi individuel",
            'these' => $these,
            'form' => $form,
        ]);
        $vm->setTemplate('comite-suivi-individuel/membre/modifier');
        return $vm;
    }

    public function modifierAction() : ViewModel
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi-individuel/membre/modifier', ['membre' => $membre->getId()], [], true));
        $form->bind($membre);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {

                $this->getMembreService()->create($membre);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'un membre du comité de suivi individuel",
            'form' => $form,
        ]);
        $vm->setTemplate('comite-suivi-individuel/membre/modifier');
        return $vm;
    }

    public function supprimerAction() : ViewModel
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getMembreService()->delete($membre);
            exit();
        }

        $vm = new ViewModel();
        if ($membre !== null) {
            $vm->setTemplate('comite-suivi-individuel/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression du membre " . $membre->getDenomination(),
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('comite-suivi-individuel/membre/supprimer', ["membre" => $membre->getId()], [], true),
            ]);
        }
        return $vm;
    }
}