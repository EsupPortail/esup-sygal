<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Substitution\Service\Log\LogServiceAwareTrait;
use Substitution\TypeAwareTrait;

class LogController extends AbstractActionController
{
    use TypeAwareTrait;
    use LogServiceAwareTrait;

    public function accueilAction(): array
    {
        return [];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function listerAction(): ViewModel
    {
        $type = $this->getRequestedType();

        return new ViewModel([
            'type' => $type,
            'result' => $this->logService->findAllLogsForType($type, $this->params()->fromQuery(), 1000),
            'operations' => $this->logService->findDistinctLogsOperationsForType($type)
        ]);
    }
}