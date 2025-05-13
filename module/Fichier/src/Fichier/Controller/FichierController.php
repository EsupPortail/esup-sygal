<?php

namespace Fichier\Controller;

use Application\Controller\AbstractController;
use Application\RouteMatch;
use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Exception;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Form\FichierFormAwareTrait;
use Fichier\Provider\Privilege\FichierPrivileges;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use UnicaenApp\Exception\RuntimeException;
use UnicaenSearch\Controller\SearchControllerInterface;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchServiceAwareTrait;
use UnicaenSearch\View\Helper\Sortable;

/**
 * @property \Fichier\Service\Fichier\Search\FichierSearchService $searchService
 */
class FichierController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use FichierFormAwareTrait;
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
            'fichierUrlPlugin' => $this->urlFichier(),
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
     */
    public function filtersAction(): ViewModel
    {
        $this->restrictFilters();

        $filters = $this->filters();

        return new ViewModel([
            'filters' => $filters,
        ]);
    }

    public function listerFichiersCommunsAction(): Response|ViewModel
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

        $dir = $this->params()->fromQuery('direction', Sortable::ASC);
        $maxi = $this->params()->fromQuery('maxi', 40);
        $page = $this->params()->fromQuery('page', 1);

        $qb = $this->fichierService->getRepository()->createQueryBuilder('f');
        $qb
            ->addSelect('n, hc, i')
            ->join('f.histoCreateur', 'hc')
            ->join('hc.individu', 'i')
            ->join('f.nature', 'n', Join::WITH, 'n.code = :code')
            ->setParameter('code', NatureFichier::CODE_COMMUNS)
            ->andWhereNotHistorise('f');

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

    public function televerserFichiersCommunsAction(): Response
    {
        if (!$this->getRequest()->isPost()) {
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

    public function telechargerAction(): void
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

    public function telechargerPermanentAction(): void
    {
        $idPermanent = $this->params()->fromRoute('idPermanent');
        /** @var Fichier|null $fichier */
        $fichier = $this->fichierService->getRepository()->findOneBy(['idPermanent' => $idPermanent]);
        if ($fichier === null) {
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

    public function afficherAction(): Response|ViewModel
    {
        $fichier = $this->requestFichier();

        if (!$this->isAllowed($fichier, FichierPrivileges::privilegeTelechargerFor($fichier->getNature()))) {
            throw new UnAuthorizedException("Visualisation non autorisée.");
        }

        $viewModel = new ViewModel();
        $viewModel->setVariables([
            'fichier' => $fichier,
            'fichierUrlPlugin' => $this->urlFichier(),
        ]);

        return $viewModel;
    }

    public function modifierAction(): Response|ViewModel
    {
        $fichier = $this->requestFichier();

        if (!$this->isAllowed($fichier, FichierPrivileges::privilegeTeleverserFor($fichier->getNature()))) {
            throw new UnAuthorizedException("Modification non autorisée.");
        }

        $this->fichierForm->bind($fichier);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $this->fichierForm->setData($data);
            if ($this->fichierForm->isValid()) {
                $this->fichierService->saveEntities([$fichier]);
                $this->flashMessenger()->addSuccessMessage("Fichier '{$fichier->getNom()}' modifié avec succès.");

                return $this->redirect()->toUrl($this->urlFichier()->afficherFichier($fichier));
            }
        }

        $this->fichierForm->setAttribute('action', $this->urlFichier()->modifierFichier($fichier));

        $viewModel = new ViewModel();
        $viewModel->setVariables([
            'form' => $this->fichierForm,
            'fichierUrlPlugin' => $this->urlFichier(),
        ]);

        return $viewModel;
    }

    public function supprimerAction(): Response
    {
        $fichier = $this->requestFichier();

        if (!$this->isAllowed($fichier, FichierPrivileges::privilegeTeleverserFor($fichier->getNature()))) {
            throw new UnAuthorizedException("Suppression non autorisée.");
        }

        $this->fichierService->supprimerFichiers([$fichier]);

        $this->flashMessenger()->addSuccessMessage("Fichier '{$fichier->getNomOriginal()}' supprimé avec succès.");

        return $this->redirect()->toUrl($this->urlFichier()->listerFichiersCommuns());
    }

    private function requestFichier(): Fichier
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getFichier();
    }
}
