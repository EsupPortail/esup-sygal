<?php

namespace Soutenance\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SoutenanceController extends AbstractActionController {

    public function indexAction()
    {
        return new ViewModel([
            ]
        );
    }
}

