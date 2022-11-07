<?php

namespace Soutenance\Controller;

use Application\Controller\AbstractController;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * @property \Soutenance\Service\Proposition\PropositionSearchService $searchService
 */
class PropositionRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    /**
     * @var array
     */
    private array $queryParams = [];

    /**
     * @var array
     */
    private $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $this->restrictFilters();

        /** @see \Soutenance\Service\Proposition\PropositionSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        return new ViewModel([
            'propositions' => $paginator,
            'role' => $this->userContextService->getSelectedIdentityRole(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictFilters();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
        ]);
        $vm->setTemplate('soutenance/proposition-recherche/filters');

        return $vm;
    }

    private function restrictFilters()
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        switch (true) {
            case $role->isEcoleDoctoraleDependant():
                $filter = $this->searchService->getEcoleDoctoraleSearchFilter();
                $entity = $role->getStructure()->getEcoleDoctorale();
                break;
            case $role->isUniteRechercheDependant():
                $filter = $this->searchService->getUniteRechercheSearchFilter();
                $entity = $role->getStructure()->getUniteRecherche();
                break;
            case $role->isEtablissementDependant():
                $filter = $this->searchService->getEtablissementInscSearchFilter();
                $entity = $role->getStructure()->getEtablissement();
                break;
            default:
                return;
        }

        $filter
            ->setData([$entity])
            ->setDefaultValueAsObject($entity)
            ->setAllowsEmptyOption(false);
    }
}