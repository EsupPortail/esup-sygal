<?php

namespace Application\Controller;

use Application\Service\Information\InformationServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class InformationController extends AbstractActionController {
    use InformationServiceAwareTrait;

    public function indexAction()
    {
        $informations = $this->getInformationService()->getInformations();
        return new ViewModel([
            'informations' => $informations,
        ]);
    }

    public function afficherAction()
    {
        $informationId  = $this->params()->fromRoute('id');
        $information    = $this->getInformationService()->getInformation($informationId);

        return new ViewModel([
            'information' => $information,
        ]);
    }

    public function ajouterAction()
    {
        return new ViewModel([]);
    }

    public function modifierAction()
    {
        return new ViewModel([]);
    }

    public function supprimerAction()
    {
        return new ViewModel([]);
    }
}
