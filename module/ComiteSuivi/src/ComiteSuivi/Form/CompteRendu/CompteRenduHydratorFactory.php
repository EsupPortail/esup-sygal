<?php

namespace ComiteSuivi\Form\CompteRendu;

use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\Membre\MembreService;
use Interop\Container\ContainerInterface;

class CompteRenduHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return CompteRenduHydrator
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var MembreService $membreService
         */
        $membreService = $container->get(MembreService::class);
        $hydrator = new CompteRenduHydrator();
        $hydrator->setMembreService($membreService);
        return $hydrator;
    }
}