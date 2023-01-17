<?php

namespace Formation\Controller\Recherche;

use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Repository\EnqueteCategorieRepositoryAwareTrait;
use Formation\Entity\Db\Repository\EnqueteQuestionRepositoryAwareTrait;
use Formation\Entity\Db\Repository\FormateurRepositoryAwareTrait;
use Formation\Entity\Db\Repository\SessionRepositoryAwareTrait;
use Formation\Entity\Db\Session;
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService;
use Individu\Entity\Db\Individu;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * @property \Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService $searchService
 */
class EnqueteReponseRechercheController extends AbstractRechercheController
{
    use SessionRepositoryAwareTrait;
    use FormateurRepositoryAwareTrait;
    use EnqueteQuestionRepositoryAwareTrait;
    use EnqueteCategorieRepositoryAwareTrait;

    const SESSION_ROUTE_PARAM_TOUTES = 'toutes';

    protected string $routeName = 'formation/enquete/resultat';
    protected string $indexActionTemplate = 'formation/enquete-reponse/recherche/afficher-resultats';
    protected string $title = "Résultats de l'enquête";

    protected ?Session $requestedSession = null;

    /**
     * Redéfinition de la méthode {@see SearchControllerTrait::search()} pour pouvoir filtrer selon la Session
     * éventuellement spécifié dans la requête.
     *
     * @return \Application\Search\SearchResultPaginator|\Laminas\Http\Response|\Laminas\Paginator\Paginator
     */
    public function search()
    {
        $session = $this->getRequestedSession();

        return $this->getSearchPluginController()->search(function (QueryBuilder $qb) use ($session) {
            if ($session !== null) {
                $qb->andWhere('sess = :session')->setParameter('session', $session);
            }
        });
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

        $model = parent::indexAction();

        if ($model instanceof Response) {
            return $model;
        }

        /** @var \Application\Search\SearchResultPaginator $reponses */
        $reponses = $model->getVariable('paginator');
        $reponses->setItemCountPerPage(-1);

        $questions = $this->enqueteQuestionRepository->findAll();
        $questions = array_filter($questions, function (EnqueteQuestion $a) {
            return $a->estNonHistorise();
        });
        usort($questions, function (EnqueteQuestion $a, EnqueteQuestion $b) {
            return $a->getOrdre() > $b->getOrdre();
        });

        /** PREP HISTOGRAMME $histogramme */
        $histogramme = [];
        foreach ($questions as $question) {
            $histogramme[$question->getId()] = [];
            foreach (EnqueteReponse::NIVEAUX as $clef => $value) $histogramme[$question->getId()][$clef] = 0;
        }

        $array = [];

        /** @var EnqueteReponse $reponse */
        foreach ($reponses as $reponse) {
            if ($reponse->getQuestion()->estNonHistorise()) {
                $question = $reponse->getQuestion()->getId();
                $inscription = $reponse->getInscription()->getId();

                $niveau = $reponse->getNiveau();
                $description = $reponse->getDescription();

                $array[$inscription]["Niveau_" . $question] = EnqueteReponse::NIVEAUX[$niveau];
                $array[$inscription]["Commentaire_" . $question] = $description;
                $histogramme[$question][$niveau]++;
            }
        }

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

        /** @var \Application\Search\Filter\SelectSearchFilter $formateurFilter */
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