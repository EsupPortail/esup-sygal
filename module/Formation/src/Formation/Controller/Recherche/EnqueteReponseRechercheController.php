<?php

namespace Formation\Controller\Recherche;

use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Repository\EnqueteCategorieRepositoryAwareTrait;
use Formation\Entity\Db\Repository\EnqueteQuestionRepositoryAwareTrait;
use Formation\Entity\Db\Repository\FormateurRepositoryAwareTrait;
use Formation\Entity\Db\Repository\SessionRepositoryAwareTrait;
use Formation\Entity\Db\Session;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceAwareTrait;
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService;
use Individu\Entity\Db\Individu;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use UnicaenSearch\Controller\SearchControllerTrait;
use UnicaenSearch\SearchResultPaginator;

/**
 * @property \Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService $searchService
 */
class EnqueteReponseRechercheController extends AbstractRechercheController
{
    use SessionRepositoryAwareTrait;
    use FormateurRepositoryAwareTrait;
    use EnqueteQuestionRepositoryAwareTrait;
    use EnqueteCategorieRepositoryAwareTrait;
    use EnqueteReponseServiceAwareTrait;
    use SearchControllerTrait { search as public searchDefault; }

    public const SESSION_ROUTE_PARAM_TOUTES = 'toutes';

    protected string $routeName = 'formation/enquete/resultat';
    protected string $indexActionTemplate = 'formation/enquete-reponse/recherche/afficher-resultats';
    protected string $title = "Résultats de l'enquête";

    protected ?Session $requestedSession = null;

    /**
     * Redéfinition de la méthode {@see SearchControllerTrait::search()} pour pouvoir filtrer selon la Session
     * éventuellement spécifié dans la requête.
     *
     * @param callable|null $queryBuilderModifierCallback
     * @return \Laminas\Http\Response|\UnicaenSearch\SearchResultPaginator
     */
    public function search(?callable $queryBuilderModifierCallback = null): Response|SearchResultPaginator
    {
        $session = $this->getRequestedSession();

        $callbcack = null;
        if ($session !== null) {
            $callbcack = function (QueryBuilder $qb) use ($session) {
                $qb->andWhere('sess = :session')->setParameter('session', $session);
            };
        }

        return $this->searchDefault($callbcack);
    }

    /**
     * Redéfinition de la méthode {@see SearchControllerTrait::filtersAction()} pour pouvoir initialiser
     * le filtre Formateur dont les valeurs dépendent de la Session éventuellement spécifié dans la requête.
     *
     * @return ViewModel
     */
    public function filtersAction(): ViewModel
    {
        $this->initFormateurFilter();

        return parent::filtersAction();
    }

    public function afficherResultatsAction()
    {
        $this->initFormateurFilter();

        $model = parent::indexAction(true);

        if ($model instanceof Response) {
            return $model;
        }

        /** @var \UnicaenSearch\SearchResultPaginator $reponses */
        $reponses = $model->getVariable('paginator');
        $reponses->setItemCountPerPage(-1); // toutes les données, i.e. pas de pagination
        $reponses = iterator_to_array($reponses);

        $questions = $this->enqueteQuestionRepository->findAll();

        [$array, $histogramme] = $this->enqueteReponseService->prepareResultatsEnquetes($reponses, $questions);

        $categories = $this->enqueteCategorieRepository->findBy([], ['ordre' => 'asc']);

        $model->setVariables([
            'session' => $this->getRequestedSession(),
            "array" => $array,
            "histogramme" => $histogramme,
            "nbReponses" => count($array),
            "questions" => $questions,
            "categories" => $categories,
        ]);

        return $model;
    }

    private function initFormateurFilter()
    {
        $session = $this->getRequestedSession();

        /** @var \UnicaenSearch\Filter\SelectSearchFilter $formateurFilter */
        $formateurFilter = $this->searchService->getFilterByName(EnqueteReponseSearchService::NAME_formateur);
        $formateurFilter->setDataProvider(
            function() use ($session) {
                if ($session !== null) {
                    $formateurs = $this->formateurRepository->findAllForSession($session);
                } else {
                    $formateurs = $this->formateurRepository->findAll();
                }
                $individus = array_unique(
                    array_map(
                        fn(Formateur $f) => $f->getIndividu(),
                        $formateurs
                    )
                );

                return Individu::asSelectValuesOptions($individus);
            }
        );
    }

    protected function getRequestedSession(): ?Session
    {
        if (null === $this->requestedSession) {
            $sessionId = $this->params()->fromRoute('session');
            if ($sessionId !== self::SESSION_ROUTE_PARAM_TOUTES) {
                $this->requestedSession = $this->sessionRepository->find($sessionId);
            }
        }

        return $this->requestedSession;
    }
}