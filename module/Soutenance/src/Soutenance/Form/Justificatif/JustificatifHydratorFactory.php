<?php

namespace Soutenance\Form\Justificatif;

use Application\Service\FichierThese\FichierTheseService;
use Soutenance\Service\Membre\MembreService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class JustificatifHydratorFactory {

    public function __invoke(HydratorPluginManager $container)
    {
        /**
         * @var FichierTheseService $fichierTheseService
         * @var MembreService $membreService
         */
        $fichierTheseService = $container->getServiceLocator()->get(FichierTheseService::class);
        $membreService = $container->getServiceLocator()->get(MembreService::class);

        $hydrator = new JusticatifHydrator();
        $hydrator->setFichierTheseService($fichierTheseService);
        $hydrator->setMembreService($membreService);
        return $hydrator;
    }
}