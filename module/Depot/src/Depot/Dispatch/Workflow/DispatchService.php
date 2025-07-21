<?php

namespace Depot\Dispatch\Workflow;

use Application\Service\Url\UrlService;
use Depot\Controller\WorkflowController;
use Laminas\Mvc\Controller\AbstractActionController;
use These\Entity\Db\These;

class DispatchService extends UrlService
{
    public function dispatchNextStepBox(AbstractActionController $controller, These $these, $etape = null, $except = null, array $queryParams = [])
    {
        if ($etape !== null) {
            $queryParams['etape'] = $etape;
        }
        if ($except !== null) {
            $queryParams['except'] = $except;
        }

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(WorkflowController::class, [
            'action' => 'next-step-box',
            'these' => $these->getId(),
            'query' => $queryParams,
        ]);
    }
}