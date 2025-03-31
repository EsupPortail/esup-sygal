<?php

namespace Formation\Controller\Recherche;

use Application\Controller\AbstractController;
use UnicaenSearch\Controller\SearchControllerInterface;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchServiceAwareTrait;
use Formation\Provider\Parametre\FormationParametres;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class AbstractRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;
    use ParametreServiceAwareTrait;

    protected string $routeName;
    protected string $indexActionTemplate;
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title;

    public function indexAction(bool $searchIfRequested = false): Response|ViewModel
    {
        $result = $searchIfRequested ? $this->searchIfRequested(): $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        $model = new ViewModel([
            'title' => $this->title,
            'paginator' => $paginator,
            'routeName' => $this->routeName,
            'returnUrl' => $this->getRequest()->getRequestUri(),
            'delai' => $this->getParametreService()?->getValeurForParametre(FormationParametres::CATEGORIE, FormationParametres::DELAI_ENQUETE),
        ]);
        $model->setTemplate($this->indexActionTemplate);

        return $model;
    }

    /**
     * Surcharge de la méthode {@see SearchControllerTrait::filtersAction()}.
     *
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
        ]);
        $model->setTemplate($this->filtersActionTemplate);

        return $model;
    }

    protected function restrictFilters(): void
    {
        // ...
    }
}