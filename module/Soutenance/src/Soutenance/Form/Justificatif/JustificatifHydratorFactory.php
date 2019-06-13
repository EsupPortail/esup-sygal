<?php

namespace Soutenance\Form\Justificatif;

use Application\Service\Fichier\FichierService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class JustificatifHydratorFactory {

    public function __invoke(HydratorPluginManager $container)
    {
        /**
         * @var FichierService $fichierService
         */
        $fichierService = $container->getServiceLocator()->get('FichierService');

        $hydrator = new JusticatifHydrator();
        $hydrator->setFichierService($fichierService);
        return $hydrator;
    }
}