<?php

namespace Doctorant\Controller;

use Application\Controller\AbstractController;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnexpectedValueException;
use UnicaenPrivilege\Provider\Privilege\Privileges;

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
//        $this->restrictFilters();

        $text = $this->params()->fromQuery('text');

        /** @see \Doctorant\Service\Search\DoctorantSearchService */
        $result = $this->searchIfRequired ? $this->searchIfRequested() : $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var SearchResultPaginator $paginator */
        $paginator = $result;

        $etablissement = true;//$this->searchService->getEtablissementInscSearchFilter()->getValue(); // todo
//        $etatThese = $this->searchService->getFilterValueByName(TheseSearchService::NAME_etatThese);

        return new ViewModel([
            'paginator' => $paginator,
            'text' => $text,
//            'roleDirecteurThese' => $this->applicationRoleService->getRepository()->findOneBy(['sourceCode' => Role::CODE_DIRECTEUR_THESE]),
            'displayEtablissement' => !$etablissement,
//            'displayDateSoutenance' => $etatThese === These::ETAT_SOUTENUE || !$etatThese,
//            'etatThese' => $etatThese,
            'loadFiltersUrl' => 'doctorant/recherche/filters',
        ]);
    }

    public function filtersAction(): ViewModel
    {
//        $this->restrictFilters();

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
//        $this->restrictFilters();

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
//        $this->restrictFilters();

        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('these/these-recherche/filters');

        return $vm;
    }

    /**
     * Méthode donnant l'occasion de modifier/restreindre les filtres de recherche
     * (ex : restreindre un select à la seule ED autorisée à l'utilisateur).
     */
    public function restrictSearchFilters(): void
    {
        if ($this->isAllowed(Privileges::getResourceId(DoctorantPrivileges::DOCTORANT_LISTER_TOUT))) {
            // aucune restriction
            return;
        }
        if (!$this->isAllowed(Privileges::getResourceId(DoctorantPrivileges::DOCTORANT_LISTER_SIEN))) {
            throw new UnexpectedValueException(
                "Anomalie : l'action aurait dû être bloquée en amont (controller guard) car l'utilisateur n'a aucun des privilèges suivants : " .
                implode(', ', [DoctorantPrivileges::DOCTORANT_LISTER_TOUT, DoctorantPrivileges::DOCTORANT_LISTER_SIEN])
            );
        }

        $this->restrictFiltersFromStructureRole();
    }

    private function restrictFiltersFromStructureRole(): void
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        if ($etab = $role->getStructure()?->getEtablissement()) {
            $filter = $this->searchService->getEtablissementInscSearchFilter();
            $filter
                ->setData([$etab])
                ->setDefaultValueAsObject($etab)
                ->setAllowsEmptyOption(false);
        }
    }
}