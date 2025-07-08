<?php

namespace Formation\Export\Xlsx\Formation;

use Application\Export\Xlsx\XlsxExportDataProviderInterface;
use Formation\Service\EnqueteQuestion\EnqueteQuestionServiceAwareTrait;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceAwareTrait;
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchServiceAwareTrait;

class EnqueteDataProvider implements XlsxExportDataProviderInterface
{
    use EnqueteQuestionServiceAwareTrait;
    use EnqueteReponseServiceAwareTrait;
    use EnqueteReponseSearchServiceAwareTrait;

    private array $context;

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function fetchData(): array
    {
        $formationsIds = $this->context['formationsIds'] ?? null;
        $sessionId = $this->context['sessionId'] ?? null;

        $questions = $this->enqueteQuestionService->getRepository()->findAll();

        $this->enqueteReponseSearchService->init();
        $this->enqueteReponseSearchService->processQueryParams($this->context);
        // todo : restreindre en fonction du rôle lorsque d'autres rôles que 'ADMIN_TECH', 'BDD', 'GEST_FORM' seront habilités !
        $qb = $this->enqueteReponseSearchService->getQueryBuilder(); /** @see \Formation\Service\Formation\Search\FormationSearchService::createQueryBuilder() */

        if ($formationsIds !== null) {
            $qb->andWhere($qb->expr()->in('form', $formationsIds));
        }
        if ($sessionId !== null) {
            $qb->andWhere('sess = :sessionId')->setParameter('sessionId', $sessionId);
        }

        $reponses = $qb->getQuery()->getResult();

        [$array, ] = $this->enqueteReponseService->prepareResultatsEnquetes($reponses, $questions);

        return $array;
    }
}