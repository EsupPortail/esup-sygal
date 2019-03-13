<?php

namespace Soutenance\Form\Membre;

use Soutenance\Service\Qualite\QualiteService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;


class MembreHydratorFactory
{
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $servicelocator = $hydratorPluginManager->getServiceLocator();
        /**
         * @var QualiteService $qualiteService
         */
        $qualiteService = $servicelocator->get(QualiteService::class);

        /** @var MembreForm $form */
        $hydrator = new MembreHydrator();
        $hydrator->setQualiteService($qualiteService);

        return $hydrator;
    }
}