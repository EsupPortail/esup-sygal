<?php

namespace Formation\Controller\Recherche;

use Application\Controller\AbstractController;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\ViewModel;

class AbstractRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    protected string $routeName;
    protected string $indexActionTemplate;
    protected string $filtersActionTemplate = 'formation/filters';
    protected string $title;

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $result = $this->search();
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
}