<?php

namespace Substitution\Controller;

use Doctorant\Controller\Plugin\UrlDoctorant;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Substitution\Service\DoublonServiceAwareTrait;
use Substitution\Service\SubstitutionServiceAwareTrait;

/**
 * @method FlashMessenger flashMessenger()
 */
class SubstitutionController extends AbstractActionController
{
    use DoublonServiceAwareTrait;
    use SubstitutionServiceAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function individuAction(): ViewModel
    {
        $result = $this->substitutionService->findAllSubstitutionsIndividu();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/substitution/individu/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function doctorantAction(): ViewModel
    {
        $result = $this->substitutionService->findAllSubstitutionsDoctorant();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/substitution/doctorant/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function structureAction(): ViewModel
    {
        $result = $this->substitutionService->findAllSubstitutionsStructure();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/substitution/structure/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function etablissementAction(): ViewModel
    {
        $result = $this->substitutionService->findAllSubstitutionsEtablissement();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/substitution/etablissement/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function ecoleDoctAction(): ViewModel
    {
        $result = $this->substitutionService->findAllSubstitutionsEcoleDoct();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/substitution/ecole-doct/liste');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function uniteRechAction(): ViewModel
    {
        $result = $this->substitutionService->findAllSubstitutionsUniteRech();

        return (new ViewModel([
            'result' => $result,
        ]))->setTemplate('substitution/substitution/unite-rech/liste');
    }
}