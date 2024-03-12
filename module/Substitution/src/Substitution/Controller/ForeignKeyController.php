<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\ForeignKey\ForeignKeyServiceAwareTrait;
use Substitution\TypeAwareTrait;
use Webmozart\Assert\Assert;

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


    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function listerEnregistrementsLiesAction(): ViewModel
    {
        $type = $this->getRequestedType();
        $substituantId = $this->getRequestedId();

        $result = $this->foreignKeyService->findAllRelatedRecordsForTypeAndForeignKeyValue($type, $substituantId);

        return new ViewModel([
            'type' => $type,
            'substituantId' => $substituantId,
            'result' => $result,
        ]);
    }

    protected function getRequestedId(): int
    {
        $id = $this->params()->fromRoute('id');
        Assert::notNull($id, "Un id doit être spécifié dans la requête.");

        return $id;
    }
}