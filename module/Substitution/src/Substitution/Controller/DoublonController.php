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

    public function accueilAction(): ViewModel
    {
        return (new ViewModel([
        ]))->setTemplate('substitution/doublon/accueil');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function individuAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsIndividu(50);
        $count = $this->doublonService->countAllDoublonsIndividu();

        return (new ViewModel([
            'result' => $result,
            'count' => $count,
        ]))->setTemplate('substitution/doublon/individu/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function doctorantAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsDoctorant(50);
        $count = $this->doublonService->countAllDoublonsDoctorant();

        return (new ViewModel([
            'result' => $result,
            'count' => $count,
        ]))->setTemplate('substitution/doublon/doctorant/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function structureAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsStructure(50);
        $count = $this->doublonService->countAllDoublonsStructure();

        return (new ViewModel([
            'result' => $result,
            'count' => $count,
        ]))->setTemplate('substitution/doublon/structure/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function etablissementAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsEtablissement(50);
        $count = $this->doublonService->countAllDoublonsEtablissement();

        return (new ViewModel([
            'result' => $result,
            'count' => $count,
        ]))->setTemplate('substitution/doublon/etablissement/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ecoleDoctAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsEcoleDoct(50);
        $count = $this->doublonService->countAllDoublonsEcoleDoct();

        return (new ViewModel([
            'result' => $result,
            'count' => $count,
        ]))->setTemplate('substitution/doublon/ecole-doct/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function uniteRechAction(): ViewModel
    {
        $result = $this->doublonService->findAllDoublonsUniteRech(50);
        $count = $this->doublonService->countAllDoublonsUniteRech();

        return (new ViewModel([
            'result' => $result,
            'count' => $count,
        ]))->setTemplate('substitution/doublon/unite-rech/liste');
    }
}