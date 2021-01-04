<?php

namespace UnicaenDeploy\Controller;

use UnicaenDeploy\Service\DeployServiceAwareTrait;
use Webmozart\Assert\Assert;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

class ConsoleController extends AbstractConsoleController
{
    use DeployServiceAwareTrait;

    public function deployAction()
    {
        $targetName = $this->params('target');
        Assert::notEmpty($targetName);

        $this->deployService->processTargetByName($targetName);

        return false;
    }
}
