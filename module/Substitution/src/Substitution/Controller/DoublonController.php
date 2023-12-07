<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\Doublon\DoublonServiceAwareTrait;
use Substitution\TypeAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class DoublonController extends AbstractActionController
{
    use TypeAwareTrait;
    use DoublonServiceAwareTrait;

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

        $result = $this->doublonService->findAllDoublonsForType($type, 50);
        $count = $this->doublonService->countAllDoublonsForType($type);

        return new ViewModel([
            'type' => $type,
            'result' => $result,
            'count' => $count,
        ]);
    }
}