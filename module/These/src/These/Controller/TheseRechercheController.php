<?php

namespace These\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use These\Entity\Db\These;
use These\Service\These\TheseSearchService;
use These\Service\These\TheseServiceAwareTrait;

/**
 * @property TheseSearchService $searchService
 */
class TheseRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use ApplicationRoleServiceAwareTrait;
    use TheseServiceAwareTrait;

    private bool $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    public function indexAction(): Response|ViewModel
    {
        $this->restrictFiltersByStructure();

        $text = $this->params()->fromQuery('text');

        /** @see TheseSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        $etablissement = $this->searchService->getEtablissementInscSearchFilter()->getValue();
        $etatThese = $this->searchService->getFilterValueByName(TheseSearchService::NAME_etatThese);

        return new ViewModel([
            'theses' => $paginator,
            'text' => $text,
            'roleDirecteurThese' => $this->applicationRoleService->getRepository()->findOneBy(['sourceCode' => Role::CODE_DIRECTEUR_THESE]),
            'displayEtablissement' => !$etablissement,
            'displayDateSoutenance' => $etatThese === These::ETAT_SOUTENUE || !$etatThese,
            'etatThese' => $etatThese,
            'filtersRoute' => 'these/recherche/filters',
        ]);
    }

    public function indexFiltersAction(): ViewModel
    {
        $this->restrictFiltersByStructure();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('these/these-recherche/filters');

        return $vm;
    }

    /**
     * Prévu pour ED, UR, MDD.
     *
     * @return ViewModel
     */
    public function notresAction()
    {
        $this->restrictFilters();

        $this->searchIfRequired = false;

        $viewModel = $this->indexAction();
        $viewModel->setTemplate('these/these-recherche/index');
        $viewModel->setVariables([
            'filtersRoute' => 'these/recherche/notres/filters',
        ]);

        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    public function notresFiltersAction(): ViewModel
    {
        $this->restrictFilters();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('these/these-recherche/filters');

        return $vm;
    }

    private function restrictFilters()
    {
        /** @var Role $role */
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

    private function restrictFiltersByStructure(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($role->isEcoleDoctoraleDependant()) {
            $filter = $this->searchService->getEcoleDoctoraleSearchFilter();
            $sc = $role->getStructure()->getEcoleDoctorale();
            $filter->setDefaultValueAsObject($sc);
        } elseif ($role->isUniteRechercheDependant()) {
            $filter = $this->searchService->getUniteRechercheSearchFilter();
            $sc = $role->getStructure()->getUniteRecherche();
            $filter->setDefaultValueAsObject($sc);
        } elseif ($role->isEtablissementDependant()) {
            $filter = $this->searchService->getEtablissementInscSearchFilter();
            $sc = $role->getStructure()->getEtablissement();
            $filter->setDefaultValueAsObject($sc);
        }
    }
}