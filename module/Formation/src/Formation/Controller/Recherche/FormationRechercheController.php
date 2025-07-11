<?php

namespace Formation\Controller\Recherche;

use Application\Export\Xlsx\Manager\XlsxExportManagerAwareTrait;
use Formation\Service\Formation\Search\FormationSearchService;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * @property \Formation\Service\Formation\Search\FormationSearchService $searchService
 */
class FormationRechercheController extends AbstractRechercheController
{
    use XlsxExportManagerAwareTrait;

    protected string $routeName = 'formation/formation';
    protected string $indexActionTemplate = 'formation/formation/recherche/index';
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title = "Formations";

    public function indexAction(bool $searchIfRequested = false): Response|ViewModel
    {
        $model = parent::indexAction();
        if ($model instanceof Response) {
            return $model;
        }

        $model->setVariables([
            'exports' => $this->formationXlsxExportManager->getAll(),
        ]);

        return $model;
    }

//    protected function restrictFilters(): void
//    {
//        parent::restrictFilters();
//
//        /** @var \UnicaenSearch\Filter\SelectSearchFilter $f */
//        $filter = $this->searchService->getFilterByName('responsable');
//        if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleEcoleDoctorale()) {
//            $ed = $roleEcoleDoctorale->getStructure()->getEcoleDoctorale();
//            $filter->setData([$ed]);
//            $filter->setDefaultValueAsObject($ed);
//            $filter->setAllowsEmptyOption(false);
//        } elseif ($this->userContextService->getSelectedRoleDirecteurThese()) {
//            $filter->setVisible(false);
//        }
//    }

    public function exporterXlsAction()
    {
        $code = $this->params()->fromRoute('code');
        if (!$this->formationXlsxExportManager->has($code)) {
            return $this->redirect()->toUrl($this->params()->fromQuery('redirect', '/'));
        }

        /** @var \Application\Export\Xlsx\XlsxExportInterface $export */
        $export = $this->formationXlsxExportManager->get($code);

        $context = [];
        if ($formationId = $this->params()->fromRoute('formation')) {
            // Cas 1 : une formation est spécifiée dans la route
            $context['formationsIds'] = [$formationId];
        } else {
            // Cas 2 : recherche avec prise en compte des query params courants
            $queryParams = $this->params()->fromQuery();
            $this->searchService->init();
            $this->searchService->processQueryParams($queryParams);
            // todo : restreindre en fonction du rôle lorsque d'autres rôles que 'ADMIN_TECH', 'BDD', 'GEST_FORM' seront habilités !
            $qb = $this->searchService->getQueryBuilder(); /** @see \Formation\Service\Formation\Search\FormationSearchService::createQueryBuilder() */
            $formationIds = array_keys($qb->select('f.id')->indexBy('f', 'f.id')->getQuery()->getArrayResult());
            if (count($formationIds) === 0) {
                $this->flashMessenger()->addErrorMessage("Aucune donnée à exporter, vérifiez les critères de recherche !");
                return $this->redirect()->toRoute('formation/formation', [], ['query' => $this->params()->fromQuery(), true]);
            }

            $context['formationsIds'] = $formationIds;

            if ($anneeUniv = $this->searchService->getFilterValueByName(FormationSearchService::NAME_anneeUniv)) {
                $context['anneeUniv'] = $anneeUniv;
            }
        }

        if ($sessionId = $this->params()->fromRoute('session')) {
            $context['sessionId'] = $sessionId;
        }

        $export->setContext($context);
        //$export->exportToFile(sys_get_temp_dir().'/formation.xlsx');
        $export->exportToClient();
        exit(0);
    }
}