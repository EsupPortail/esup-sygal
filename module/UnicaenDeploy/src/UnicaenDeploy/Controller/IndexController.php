<?php

namespace UnicaenDeploy\Controller;

use UnicaenDeploy\Service\DeployServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    use DeployServiceAwareTrait;

    public function indexAction()
    {
//        $this->deployService->processTargetByName('dev');
        $this->deployService->processTargetByName('preprod');

        return false;
    }
}
