<?php

namespace Soutenance\Form\Justificatif;

use Application\Service\FichierThese\FichierTheseService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class JustificatifHydratorFactory {

    public function __invoke(HydratorPluginManager $container)
    {
        /**
         * @var FichierTheseService $fichierTheseService
         */
        $fichierTheseService = $container->getServiceLocator()->get(FichierTheseService::class);

        $hydrator = new JusticatifHydrator();
        $hydrator->setFichierTheseService($fichierTheseService);
        return $hydrator;
    }
}