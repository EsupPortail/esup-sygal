<?php

namespace Fichier\Controller;

use Application\Controller\AbstractController;
use Application\RouteMatch;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\View\Helper\Sortable;
use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Exception;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Provider\Privilege\FichierPrivileges;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Exception\RuntimeException;

/**
 * @property \Fichier\Service\Fichier\Search\FichierSearchService $searchService
 */
class FichierController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;

    public function indexAction(): Response|ViewModel
    {
        $this->restrictFilters();

        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        return new ViewModel([
            'paginator' => $paginator,
            'filters' => $this->filters(),
            'urlFichierPlugin' => $this->urlFichier(),
        ]);
    }

    private function restrictFilters(): void
    {
        // filtre "nature de fichier"
        $natureFilter = $this->searchService->getNatureFichierSearchFilter();
        $natures = $natureFilter->getData();
        $naturesAutorisees = [];
        foreach ($natures as $nature) {
            try {
                $privilege = FichierPrivileges::privilegeTelechargerFor($nature);
            } catch (Exception $e) {
                continue;
            }
            if ($this->isAllowed(FichierPrivileges::getResourceId($privilege))) {
                $naturesAutorisees[] = $nature;
            }
        }
        if (empty($naturesAutorisees)) {
            $natureFilter->setAllowsEmptyValueOption(false);
        }
        $natureFilter->setData($naturesAutorisees);
    }

    /**
     * Surcharge de la méthode {@see SearchControllerTrait::filtersAction()}.
     *
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictFilters();

        $filters = $this->filters();

        $model = new ViewModel([
            'filters' => $filters,
        ]);

        return $model;
    }

    public function listerFichiersCommunsAction()
    {
        /**
         * Application des filtres et tris par défaut.
         */
        $needsRedirect = false;
        $queryParams = $this->params()->fromQuery();
        $sort = $this->params()->fromQuery('sort');
        if ($sort === null) { // null <=> paramètre absent
            // tri par défaut
            $queryParams = array_merge($queryParams, ['sort' => 'f.nom', 'direction' => Sortable::ASC]);
            $needsRedirect = true;
        }
        if ($needsRedirect) {
            return $this->redirect()->toRoute(null, [], ['query' => $queryParams], true);
        }

        $dir  = $this->params()->fromQuery('direction', Sortable::ASC);
        $maxi = $this->params()->fromQuery('maxi', 40);
        $page = $this->params()->fromQuery('page', 1);

        $qb = $this->fichierService->getRepository()->createQueryBuilder('f');
        $qb
            ->addSelect('n, hc, i')
            ->join('f.histoCreateur', 'hc')
            ->join('hc.individu', 'i')
            ->join('f.nature', 'n', Join::WITH, 'n.code = :code')
            ->setParameter('code', NatureFichier::CODE_COMMUNS);

        foreach (explode('+', $sort) as $sortProp) {
            $qb->addOrderBy($sortProp, $dir);
        }

        $paginator = new \Laminas\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(20)
            ->setItemCountPerPage((int)$maxi)
            ->setCurrentPageNumber((int)$page);

        return new ViewModel([
            'fichiers' => $paginator,
            'urlFichierPlugin' => $this->urlFichier(),
        ]);
    }

    public function televerserFichiersCommunsAction()
    {
        if (! $this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->urlFichier()->listerFichiersCommuns());
        }

        $result = $this->uploader()->upload();

        if ($result instanceof JsonModel) {
            return $this->redirect()->toUrl($this->urlFichier()->listerFichiersCommuns());
        }

        $fichiers = $this->fichierService->createFichiersFromUpload($result, NatureFichier::CODE_COMMUNS);
        $this->fichierService->saveFichiers($fichiers);

        return $this->redirect()->toUrl($this->urlFichier()->listerFichiersCommuns());
    }

    public function telechargerAction()
    {
        $fichier = $this->requestFichier();

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        $this->uploader()->download($fichier);
    }

    public function telechargerPermanentAction()
    {
        $idPermanent = $this->params()->fromRoute('idPermanent');
        /** @var Fichier|null $fichier */
        $fichier = $this->fichierService->getRepository()->findOneBy(['idPermanent' => $idPermanent]);
        if( $fichier === null) {
            throw new RuntimeException("Fichier introuvable en base de données");
        }

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $this->fichierStorageService->setGenererFichierSubstitutionSiIntrouvable(false);
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        $this->uploader()->download($fichier);
    }

    public function supprimerAction()
    {
        $fichier = $this->requestFichier();

        if (! $this->isAllowed($fichier, FichierPrivileges::privilegeTeleverserFor($fichier->getNature()))) {
            throw new UnAuthorizedException("Suppression non autorisée.");
        }

        $result = $this->confirm()->execute();

        // si un tableau est retourné par le plugin Confirm, l'opération a été confirmée
        if (is_array($result)) {
            $this->fichierService->supprimerFichiers([$fichier]);

            $this->flashMessenger()->addSuccessMessage("Fichier '{$fichier->getNomOriginal()}' supprimé avec succès.");

            return $this->redirect()->toUrl($this->urlFichier()->listerFichiersCommuns());
        }

        $viewModel = $this->confirm()->getViewModel();
        $viewModel->setVariables([
            'title'   => "Suppression d'un fichier",
            'fichier' => $fichier,
        ]);

        return $viewModel;


    }

    /**
     * @return Fichier
     */
    private function requestFichier()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getFichier();
    }
}
