<?php

namespace Formation\Controller;

use Formation\Entity\Db\SessionStructureValide;
use Formation\Form\SessionStructureValide\SessionStructureValideFormAwareTrait;
use Formation\Service\Session\SessionServiceAwareTrait;
use Formation\Service\SessionStructureValide\SessionStructureValideServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class SessionStructureValideController extends AbstractActionController {
    use SessionServiceAwareTrait;
    use SessionStructureValideServiceAwareTrait;

    use SessionStructureValideFormAwareTrait;

    public function ajouterStructureComplementaireAction() : ViewModel
    {
        $session = $this->getSessionService()->getRepository()->getRequestedSession($this);

        $structureComplementaire = new SessionStructureValide();
        $structureComplementaire->setSession($session);

        $form = $this->getSessionStructureValideForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/ajouter-structure-complementaire', ['session' => $session->getId()], [], true));
        $form->bind($structureComplementaire);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionStructureValideService()->create($structureComplementaire);
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
        $structureComplementaire = $this->getSessionStructureValideService()->getRepository()->getRequestedSessionStructureValide($this);

        $form = $this->getSessionStructureValideForm();
        $form->setAttribute('action', $this->url()->fromRoute('formation/session/modifier-structure-complementaire', ['structure-complementaire' => $structureComplementaire->getId()], [], true));
        $form->bind($structureComplementaire);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getSessionStructureValideService()->update($structureComplementaire);
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
        $structureComplementaire = $this->getSessionStructureValideService()->getRepository()->getRequestedSessionStructureValide($this);
        $this->getSessionStructureValideService()->historise($structureComplementaire);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $structureComplementaire->getSession()->getId()], [], true);
    }

    public function restaurerStructureComplementaireAction() : Response
    {
        $structureComplementaire = $this->getSessionStructureValideService()->getRepository()->getRequestedSessionStructureValide($this);
        $this->getSessionStructureValideService()->restore($structureComplementaire);

        $retour = $this->params()->fromQuery('retour');
        if ($retour) return $this->redirect()->toUrl($retour);
        return $this->redirect()->toRoute('formation/session/afficher', ['session' => $structureComplementaire->getSession()->getId()], [], true);
    }

    public function supprimerStructureComplementaireAction() : ViewModel
    {
        $structureComplementaire = $this->getSessionStructureValideService()->getRepository()->getRequestedSessionStructureValide($this);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getSessionStructureValideService()->delete($structureComplementaire);
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