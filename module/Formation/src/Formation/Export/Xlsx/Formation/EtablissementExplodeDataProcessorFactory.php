<?php

namespace Formation\Export\Xlsx\Formation;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class EtablissementExplodeDataProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EtablissementExplodeDataProcessor
    {
        $export = new EtablissementExplodeDataProcessor($options);

        /** @var \Structure\Service\Etablissement\EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $export->setEtablissements($etablissementService->getRepository()->findAllEtablissementsInscriptions());

        return $export;
    }
}