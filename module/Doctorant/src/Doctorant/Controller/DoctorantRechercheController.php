<?php

namespace Doctorant\Controller;

use Application\Controller\AbstractController;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnexpectedValueException;
use UnicaenPrivilege\Provider\Privilege\Privileges;
use UnicaenSearch\Controller\SearchControllerInterface;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchResultPaginator;
use UnicaenSearch\SearchServiceAwareTrait;

/**
 * @property \Doctorant\Service\Search\DoctorantSearchService $searchService
 */
class DoctorantRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use ApplicationRoleServiceAwareTrait;
    use DoctorantServiceAwareTrait;

    private bool $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    public function indexAction(): Response|ViewModel
    {
        $this->restrictFiltersFromStructureRole();

        $text = $this->params()->fromQuery('text');

        /** @see \Doctorant\Service\Search\DoctorantSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        return new ViewModel([
            'paginator' => $paginator,
            'text' => $text,
            'loadFiltersUrl' => 'doctorant/recherche/filters',
        ]);
    }

    public function filtersAction(): ViewModel
    {
        $this->restrictFiltersFromStructureRole();

        $filters = $this->filters();

        return new ViewModel([
            'filters' => $filters,
        ]);
    }

    /**
     * Méthode donnant l'occasion de modifier le query builder selon le contexte (rôle, etc.)
     */
    public function updateSearchQueryBuilder(QueryBuilder $qb): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($role->isDoctorant()) {
            // théoriquement, le rôle Doctorant ne devrait pas avoir accès à la recherche de doctorants, mais au cas où...
            $qb->andWhere('d = :doctorant')->setParameter('doctorant', $this->userContextService->getIdentityDoctorant());
        }
        elseif ($role->isDirecteurThese() || $role->isCodirecteurThese()) {
            $qb
                ->andWhere('act.individu = :individu')->setParameter('individu', $this->userContextService->getIdentityIndividu())
                ->andWhere('actr.code = :codeRole')->setParameter('codeRole', $role->getCode());
        }
        elseif ($sc = $role->getStructure()?->getEtablissement()) {
            $qb->andWhere('etab = :etab')->setParameter('etab', $sc);
        }
        elseif ($sc = $role->getStructure()?->getEcoleDoctorale()) {
            $qb->andWhere('these.ecoleDoctorale = :ed')->setParameter('ed', $sc);
        }
        elseif ($sc = $role->getStructure()?->getUniteRecherche()) {
            $qb->andWhere('these.uniteRecherche = :ur')->setParameter('ur', $sc);
        }
    }

    /**
     * Prévu pour ED, UR, MDD.
     *
     * @return ViewModel
     */
    public function notresAction()
    {
        $this->restrictFiltersFromStructureRole();

        $this->searchIfRequired = false;

        $viewModel = $this->indexAction();
        $viewModel->setTemplate('doctorant/doctorant-recherche/index');
        $viewModel->setVariables([
            'filtersRoute' => 'doctorant/recherche/notres/filters',
        ]);

        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    public function notresFiltersAction(): ViewModel
    {
        $this->restrictFiltersFromStructureRole();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('doctorant/recherche/notres/filters');

        return $vm;
    }

    private function restrictFiltersFromStructureRole(): void
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
            ->setAllowsEmptyValueOption(false);
    }
}