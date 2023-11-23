<?php

namespace Substitution\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Substitution\Service\LogServiceAwareTrait;

class LogController extends AbstractActionController
{
    use LogServiceAwareTrait;

    public function accueilAction(): ViewModel
    {
        return (new ViewModel([
        ]))->setTemplate('substitution/log/accueil');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function individuAction(): ViewModel
    {
        return (new ViewModel([
            'result' => $this->logService->findAllLogsIndividu($this->params()->fromQuery()),
            'operations' => $this->logService->findDistinctLogsOperationsIndividu()
        ]))->setTemplate('substitution/log/individu/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function doctorantAction(): ViewModel
    {
        return (new ViewModel([
            'result' => $this->logService->findAllLogsDoctorant($this->params()->fromQuery()),
            'operations' => $this->logService->findDistinctLogsOperationsDoctorant()
        ]))->setTemplate('substitution/log/doctorant/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function structureAction(): ViewModel
    {
        return (new ViewModel([
            'result' => $this->logService->findAllLogsStructure($this->params()->fromQuery()),
            'operations' => $this->logService->findDistinctLogsOperationsStructure()
        ]))->setTemplate('substitution/log/structure/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function etablissementAction(): ViewModel
    {
        return (new ViewModel([
            'result' => $this->logService->findAllLogsEtablissement($this->params()->fromQuery()),
            'operations' => $this->logService->findDistinctLogsOperationsEtablissement()
        ]))->setTemplate('substitution/log/etablissement/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ecoleDoctAction(): ViewModel
    {
        return (new ViewModel([
            'result' => $this->logService->findAllLogsEcoleDoct($this->params()->fromQuery()),
            'operations' => $this->logService->findDistinctLogsOperationsEcoleDoct()
        ]))->setTemplate('substitution/log/ecole-doct/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function uniteRechAction(): ViewModel
    {
        return (new ViewModel([
            'result' => $this->logService->findAllLogsUniteRech($this->params()->fromQuery()),
            'operations' => $this->logService->findDistinctLogsOperationsUniteRech()
        ]))->setTemplate('substitution/log/unite-rech/liste');
    }
}