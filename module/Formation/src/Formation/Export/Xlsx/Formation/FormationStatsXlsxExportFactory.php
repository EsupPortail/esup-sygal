<?php

namespace Formation\Export\Xlsx\Formation;

use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService;
use Formation\Service\Formation\FormationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FormationStatsXlsxExportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FormationStatsXlsxExport
    {
        $export = new FormationStatsXlsxExport();
        $export->setConfig($options);

        /** @var FormationService $formationService */
        $formationService = $container->get(FormationService::class);
        $export->setFormationService($formationService);

        /** @var \Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService $searchService */
        $searchService = $container->get(EnqueteReponseSearchService::class);
        $export->setEnqueteReponseSearchService($searchService);

        return $export;
    }
}