<?php

namespace Application\Controller;

use Application\Entity\Db\RapportAnnuel;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerPlugin;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\RapportAnnuel\RapportAnnuelSearchService;
use Zend\Http\Response;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\View\Model\ViewModel;

/**
 * Class RapportAnnuelRechercheController
 */
class RapportAnnuelRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use FichierServiceAwareTrait;

    /**
     * @var RapportAnnuelSearchService
     */
    protected $searchService;

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $text = $this->params()->fromQuery('text');

        $result = $this->getSearchPluginController()->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var ZendPaginator $paginator */
        $paginator = $result;

        return new ViewModel([
            'paginator' => $paginator,
            'text' => $text,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function filtersAction()
    {
        $filters = $this->getSearchPluginController()->filters();

        return new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
    }

    /**
     * @return void|Response
     */
    public function telechargerZipAction()
    {
        $result = $this->getSearchPluginController()->search();
        if ($result instanceof Response) {
            return $result; // théoriquement, on ne devrait pas arriver ici.
        }
        /** @var ZendPaginator $paginator */
        $paginator = $result;

        $fichiers = [];
        /** @var RapportAnnuel $rapportAnnuel */
        foreach ($paginator as $rapportAnnuel) {
            $fichier = $rapportAnnuel->getFichier();
            $fichier->setPath($rapportAnnuel->generateInternalPathForZipArchive());
            $fichiers[] = $rapportAnnuel->getFichier();
        }

        $fichierZip = $this->fichierService->compresserFichiers($fichiers);
        $this->fichierService->telechargerFichier($fichierZip);
    }

    /**
     * @return SearchControllerPlugin
     */
    protected function getSearchPluginController()
    {
        /** @var SearchControllerPlugin $searchControllerPlugin */
        $searchControllerPlugin = $this->getPluginManager()->get('searchControllerPlugin');
        $searchControllerPlugin->setSearchService($this->searchService);

        return $searchControllerPlugin;
    }
}