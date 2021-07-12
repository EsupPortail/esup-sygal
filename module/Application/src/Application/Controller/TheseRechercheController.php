<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchResultPaginator;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseSearchService;
use Application\Service\These\TheseServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * Class TheseRechercheController
 *
 * @property TheseSearchService $searchService
 */
class TheseRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;

    /**
     * @var array
     */
    private $queryParams;

    /**
     * @var array
     */
    private $searchIfRequired = false; // todo : ne pas mettre à true car impossible de dépasser la page 1 !! :-(

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $text = $this->params()->fromQuery('text');

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
            'roleDirecteurThese' => $this->roleService->getRepository()->findOneBy(['sourceCode' => Role::CODE_DIRECTEUR_THESE]),
            'displayEtablissement' => !$etablissement,
            'displayDateSoutenance' => $etatThese === These::ETAT_SOUTENUE || !$etatThese,
            'etatThese' => $etatThese,
            'filtersRoute' => 'these/recherche/filters',
        ]);
    }

    /**
     * @return ViewModel
     */
    public function indexFiltersAction(): ViewModel
    {
        $filters = $this->filters();

        $vm = new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
        $vm->setTemplate('application/these-recherche/filters');

        return $vm;
    }

//    /**
//     * Pour les acteurs de thèses en général (Doctorant, Dir, Codir, etc.)
//     *
//     * @return ViewModel|Response
//     */
//    public function miennesAction()
//    {
//        /** @var Role $role */
//        $role = $this->userContextService->getSelectedIdentityRole();
//        $individu = $this->userContextService->getIdentityIndividu();
//        $etats = [These::ETAT_EN_COURS];
//
//        switch (true) {
//            case $role->isDoctorant() :
//                $theses = $this->theseService->getRepository()->findThesesByDoctorantAsIndividu($individu, $etats);
//                break;
//            case $role->isActeurDeThese() :
//                $theses = $this->theseService->getRepository()->findThesesByActeur($individu, $role, $etats);
//                break;
//            default:
//                return $this->redirect()->toRoute('home');
//        }
//
//        return new ViewModel([
//            'theses' => $theses,
//        ]);
//    }

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
        $viewModel->setTemplate('application/these-recherche/index');
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
        $vm->setTemplate('application/these-recherche/filters');

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
}