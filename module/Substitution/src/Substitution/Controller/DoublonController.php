<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\DoublonServiceAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class DoublonController extends AbstractActionController
{
    use DoublonServiceAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function individuAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsIndividu();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/doublon/individu/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function doctorantAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsDoctorant();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/doublon/doctorant/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function structureAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsStructure();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/doublon/structure/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function etablissementAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsEtablissement();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/doublon/etablissement/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ecoleDoctAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsEcoleDoct();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/doublon/ecole-doct/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function uniteRechAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsUniteRech();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/doublon/unite-rech/liste');
    }
}