<?php

namespace Application\Controller;

use Application\Form\RechercherCoEncadrantFormAwareTrait;
use Zend\Form\Element\Button;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CoEncadrantController extends AbstractActionController {
    use RechercherCoEncadrantFormAwareTrait;

    public function indexAction()
    {
        $form = $this->getRechercherCoEncadrantForm();
        $form->setAttribute('action', $this->url()->fromRoute('co-encadrant', [], [], true));
        //todo !doit remonter un acteur
        $form->setUrlCoEncadrant($this->url()->fromRoute('utilisateur/rechercher-individu', [], [], true));
        $form->get('bouton')->setLabel("Afficher l'historique de co-encadrement");

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['co-encadrant']['id'] !== "") {
                $this->redirect()->toRoute('co-encadrant/historique',['co-encadrant' => $data['co-encadrant']['id']]);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function historiqueAction()
    {
        $coencadrant = null;

        return new ViewModel([
            'coencadrant' => $coencadrant,
        ]);
    }
}