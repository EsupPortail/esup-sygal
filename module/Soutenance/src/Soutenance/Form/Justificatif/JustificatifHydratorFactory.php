<?php

namespace Soutenance\Form\Justificatif;

use Application\Service\FichierThese\FichierTheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;

class JustificatifHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return JusticatifHydrator
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var FichierTheseService $fichierTheseService
         * @var MembreService $membreService
         */
        $fichierTheseService = $container->get(FichierTheseService::class);
        $membreService = $container->get(MembreService::class);

        $hydrator = new JusticatifHydrator();
        $hydrator->setFichierTheseService($fichierTheseService);
        $hydrator->setMembreService($membreService);
        return $hydrator;
    }
}