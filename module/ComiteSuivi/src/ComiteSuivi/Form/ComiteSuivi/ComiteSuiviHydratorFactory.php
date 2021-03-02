<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;

class ComiteSuiviHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return ComiteSuiviHydrator
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get('TheseService');

        $hydrator = new ComiteSuiviHydrator();
        $hydrator->setTheseService($theseService);
        return $hydrator;
    }
}