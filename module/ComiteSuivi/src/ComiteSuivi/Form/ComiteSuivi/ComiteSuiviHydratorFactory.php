<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use Application\Service\These\TheseService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class ComiteSuiviHydratorFactory {

    /**
     * @param HydratorPluginManager $manager
     * @return ComiteSuiviHydrator
     */
    public function __invoke(HydratorPluginManager $manager)
    {
        /**
         * @var TheseService $theseService
         */
        $theseService = $manager->getServiceLocator()->get('TheseService');

        /** @var ComiteSuiviHydrator $hydrator */
        $hydrator = new ComiteSuiviHydrator();
        $hydrator->setTheseService($theseService);
        return $hydrator;
    }
}