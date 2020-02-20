<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\Membre\MembreService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class CompteRenduHydratorFactory {

    /**
     * @param HydratorPluginManager $manager
     * @return CompteRenduHydrator
     */
    public function __invoke(HydratorPluginManager $manager)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var MembreService $membreService
         */
        $comiteSuiviService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $membreService = $manager->getServiceLocator()->get(MembreService::class);

        /** @var CompteRenduHydrator $hydrator */
        $hydrator = new CompteRenduHydrator();
        $hydrator->setComiteSuiviService($comiteSuiviService);
        $hydrator->setMembreService($membreService);
        return $hydrator;
    }
}