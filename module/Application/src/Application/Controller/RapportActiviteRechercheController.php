<?php

namespace Application\Controller;

use Application\Entity\Db\Rapport;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Rapport\RapportSearchService;
use Application\Service\Structure\StructureServiceAwareTrait;
use Zend\Http\Response;
use Zend\Paginator\Paginator as ZendPaginator;
use Zend\View\Model\ViewModel;

/**
 * Class RapportActiviteRechercheController
 *
 * @property RapportSearchService $searchService
 */
class RapportActiviteRechercheController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;
    
    use StructureServiceAwareTrait;
    use FichierServiceAwareTrait;

    /**
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $this->restrictEcolesDoctorales();

        $text = $this->params()->fromQuery('text');

        $result = $this->search();
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
    public function filtersAction(): ViewModel
    {
        $this->restrictEcolesDoctorales();

        $filters = $this->filters();

        return new ViewModel([
            'filters' => $filters,
            'message' => "coucou!",
        ]);
    }

    private function restrictEcolesDoctorales()
    {
        $edFilter = $this->searchService->getEcoleDoctoraleSearchFilter();

        if ($this->isAllowed(new Rapport(), RapportPrivileges::RAPPORT_ACTIVITE_LISTER_TOUT)) {
            // aucune restriction sur les ED sélectionnables
        } elseif ($this->isAllowed(new Rapport(), RapportPrivileges::RAPPORT_ACTIVITE_LISTER_SIEN)) {
            // restrictions en fonction du rôle
            if ($roleEcoleDoctorale = $this->userContextService->getSelectedRoleDirecteurEcoleDoctorale()) {
                $eds = [$roleEcoleDoctorale->getStructure()->getEcoleDoctorale()];
                $edFilter->setData($eds);
                $edFilter->setAllowsEmptyOption(false);
            }
        }
    }

    /**
     * @return void|Response
     */
    public function telechargerZipAction(): Response
    {
        $result = $this->search();
        if ($result instanceof Response) {
            return $result; // théoriquement, on ne devrait pas arriver ici.
        }
        /** @var ZendPaginator $paginator */
        $paginator = $result;

        $fichiers = [];
        /** @var Rapport $rapport */
        foreach ($paginator as $rapport) {
            $fichier = $rapport->getFichier();
            $fichier->setPath($rapport->generateInternalPathForZipArchive());
            $fichiers[] = $rapport->getFichier();
        }

        $fichierZip = $this->fichierService->compresserFichiers($fichiers);
        $this->fichierService->telechargerFichier($fichierZip);
    }
}