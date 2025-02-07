<?php

namespace Formation\Export\Xlsx\Formation;

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

        return $export;
    }
}