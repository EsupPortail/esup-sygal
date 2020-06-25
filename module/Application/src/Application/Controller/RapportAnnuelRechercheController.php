<?php

namespace Application\Controller;

use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class RapportAnnuelRechercheController
 *
 * @package Application\Controller
 */
class RapportAnnuelRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchControllerTrait;

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $text = $this->params()->fromQuery('text');

        $paginator = $this->initSearch();

        return new ViewModel([
            'items' => $paginator,
            'text' => $text,
        ]);
    }

    public function rechercherAction()
    {
        $prg = $this->postRedirectGet();
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            return $prg;
        }

        $queryParams = $this->params()->fromQuery();

        if (is_array($prg)) {
            if (isset($queryParams['page'])) {
                unset($queryParams['page']);
            }
            $queryParams['text'] = $prg['text'];
        }

        return $this->redirect()->toRoute('these', [], ['query' => $queryParams]);
    }
}