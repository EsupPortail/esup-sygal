<?php

namespace Formation\Export\Xlsx\Formation;

use Formation\Service\EnqueteQuestion\EnqueteQuestionService;
use Formation\Service\EnqueteReponse\EnqueteReponseService;
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EnqueteDataProviderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EnqueteDataProvider
    {
        $facade = new EnqueteDataProvider();

        /** @var \Formation\Service\EnqueteQuestion\EnqueteQuestionService $enqueteQuestionService */
        $enqueteQuestionService = $container->get(EnqueteQuestionService::class);
        $facade->setEnqueteQuestionService($enqueteQuestionService);

        /** @var \Formation\Service\EnqueteReponse\EnqueteReponseService $enqueteReponseService */
        $enqueteReponseService = $container->get(EnqueteReponseService::class);
        $facade->setEnqueteReponseService($enqueteReponseService);

        /** @var \Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService $searchService */
        $searchService = $container->get(EnqueteReponseSearchService::class);
        $facade->setEnqueteReponseSearchService($searchService);

        return $facade;
    }
}