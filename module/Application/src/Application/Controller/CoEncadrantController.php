<?php

namespace Application\Controller;

use Application\Entity\Db\These;
use Application\Form\RechercherCoEncadrantFormAwareTrait;
use Application\Service\CoEncadrant\CoEncadrantServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CoEncadrantController extends AbstractActionController {
    use CoEncadrantServiceAwareTrait;
    use TheseServiceAwareTrait;
    use RechercherCoEncadrantFormAwareTrait;

    public function indexAction()
    {
        $form = $this->getRechercherCoEncadrantForm();
        $form->setAttribute('action', $this->url()->fromRoute('co-encadrant', [], [], true));
        //todo !doit remonter un acteur
        $form->setUrlCoEncadrant($this->url()->fromRoute('co-encadrant/rechercher-co-encadrant', [], [], true));
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

    public function rechercherCoEncadrantAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $acteurs = $this->getCoEncadrantService()->findByText($term);
            $result = [];
            foreach ($acteurs as $acteur) {
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $acteur->getIndividu()->getPrenom() . ' ' . $acteur->getIndividu()->getNomUsuel();
                $extra = ($acteur->getIndividu()->getEmail())?:$acteur->getIndividu()->getSourceCode();
                $result[$acteur->getId()] = array(
                    'id' => $acteur->getId(),   // identifiant unique de l'item
                    'label' => $label,          // libellé de l'item
                    'extra' => $extra,          // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }

    public function historiqueAction()
    {
        $coencadrant = $this->getCoEncadrantService()->getRequestedCoEncadrant($this);
        $theses = $this->getTheseService()->getRepository()->fetchThesesByCoEncadrant($coencadrant->getIndividu());

        $encours = []; $closes = [];
        foreach ($theses as $these) {
            if ($these->getEtatThese() === These::ETAT_EN_COURS) {
                $encours[] = $these;
            } else {
                $closes[] = $these;
            }
        }

        return new ViewModel([
            'coencadrant' => $coencadrant,
            'encours' => $encours,
            'closes' => $closes,
        ]);
    }
}