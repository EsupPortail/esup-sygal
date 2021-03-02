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
         * @var ComiteSuiviService $comiteSuiviService
         * @var MembreService $membreService
         */
        $comiteSuiviService = $container->get(ComiteSuiviService::class);
        $membreService = $container->get(MembreService::class);
        $hydrator = new CompteRenduHydrator();
        $hydrator->setComiteSuiviService($comiteSuiviService);
        $hydrator->setMembreService($membreService);
        return $hydrator;
    }
}