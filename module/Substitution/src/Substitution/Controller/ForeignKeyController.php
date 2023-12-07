<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\ForeignKey\ForeignKeyServiceAwareTrait;
use Substitution\TypeAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class ForeignKeyController extends AbstractActionController
{
    use TypeAwareTrait;
    use ForeignKeyServiceAwareTrait;

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

        $result = $this->foreignKeyService->findAllForeignKeysForType($type);

        return new ViewModel([
            'type' => $type,
            'result' => $result,
        ]);
    }

}