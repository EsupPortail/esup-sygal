<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\ForeignKeyServiceAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class ForeignKeyController extends AbstractActionController
{
    use ForeignKeyServiceAwareTrait;

    public function accueilAction(): ViewModel
    {
        return (new ViewModel([
        ]))->setTemplate('substitution/foreign-key/accueil');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function individuAction(): ViewModel
    {
        $result = $this->foreignKeyService->findAllForeignKeysIndividu();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/foreign-key/individu/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function doctorantAction(): ViewModel
    {
        $result = $this->foreignKeyService->findAllForeignKeysDoctorant();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/foreign-key/doctorant/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function structureAction(): ViewModel
    {
        $result = $this->foreignKeyService->findAllForeignKeysStructure();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/foreign-key/structure/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function etablissementAction(): ViewModel
    {
        $result = $this->foreignKeyService->findAllForeignKeysEtablissement();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/foreign-key/etablissement/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ecoleDoctAction(): ViewModel
    {
        $result = $this->foreignKeyService->findAllForeignKeysEcoleDoct();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/foreign-key/ecole-doct/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function uniteRechAction(): ViewModel
    {
        $result = $this->foreignKeyService->findAllForeignKeysUniteRech();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/foreign-key/unite-rech/liste');
    }
}