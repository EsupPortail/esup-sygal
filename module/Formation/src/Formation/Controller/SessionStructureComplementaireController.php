<?php

namespace Formation\Controller;

use Formation\Entity\Db\SessionStructureComplementaire;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireFormAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Formation\Service\SessionStructureComplementaire\SessionStructureComplementaireServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class SessionStructureComplementaireController extends AbstractActionController {
    use SessionServiceAwareTrait;
    use SessionStructureComplementaireServiceAwareTrait;

    use SessionStructureComplementaireFormAwareTrait;

    public function ajouterStructureComplementaireAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $structureComplementaire = new SessionStructureComplementaire();
        $structureComplementaire->setSession($session);

        $form = $this->getSessionStructureComplementaireForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/ajouter-structure-complementaire', ['session' => $session->getId()], [], true));
        $form->bind($structureComplementaire);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionStructureComplementaireService()->create($structureComplementaire);
            }
        }

        $vm = new ViewModel([
            'title' => "Ajout d'une structure complémentaire pour la session",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function modifierStructureComplementaireAction() : ViewModel
    {
        $structureComplementaire = $this->getSessionStructureComplementaireService()->getRepository()->getRequestedSessionStructureComplementaire($this);

        $form = $this->getSessionStructureComplementaireForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/modifier-structure-complementaire', ['structure-complementaire' => $structureComplementaire->getId()], [], true));
        $form->bind($structureComplementaire);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionStructureComplementaireService()->update($structureComplementaire);
            }
        }

        $vm = new ViewModel([
            'title' => "Modification d'une structure complémentaire pour la session",
            'form' => $form,
        ]);
        $vm->setTemplate('formation/default/default-form');
        return $vm;
    }

    public function historiserStructureComplementaireAction() : Response
    {
        $structureComplementaire = $this->getSessionStructureComplementaireService()->getRepository()->getRequestedSessionStructureComplementaire($this);
        $this->getSessionStructureComplementaireService()->historise($structureComplementaire);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $structureComplementaire->getSession()->getId()], [], true);
    }

    public function restaurerStructureComplementaireAction() : Response
    {
        $structureComplementaire = $this->getSessionStructureComplementaireService()->getRepository()->getRequestedSessionStructureComplementaire($this);
        $this->getSessionStructureComplementaireService()->restore($structureComplementaire);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $structureComplementaire->getSession()->getId()], [], true);
    }

    public function supprimerStructureComplementaireAction() : ViewModel
    {
        $structureComplementaire = $this->getSessionStructureComplementaireService()->getRepository()->getRequestedSessionStructureComplementaire($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getSessionStructureComplementaireService()->delete($structureComplementaire);
            exit();
        }

        $vm = new ViewModel();
        if ($structureComplementaire !== null) {
            $vm->setTemplate('formation/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de la structure complémentaire",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('formation/session/supprimer-structure-complementaire', ["structure-complementaire" => $structureComplementaire->getId()], [], true),
            ]);
        }
        return $vm;
    }
}