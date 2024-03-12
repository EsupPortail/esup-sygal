<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\Trigger\TriggerServiceAwareTrait;
use Substitution\TypeAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class TriggerController extends AbstractActionController
{
    use TypeAwareTrait;
    use TriggerServiceAwareTrait;

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

        $result = $this->triggerService->findAllTriggersForType($type);

        return new ViewModel([
            'type' => $type,
            'result' => $result,
        ]);
    }

}