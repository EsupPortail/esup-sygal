<?php

namespace ComiteSuiviIndividuel\Form\Membre;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Qualite\QualiteService;

class MembreHydratorFactory
{
    /**
     * @param ContainerInterface $container
     * @return MembreHydrator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : MembreHydrator
    {
        /**
         * @var QualiteService $qualiteService
         */
        $qualiteService = $container->get(QualiteService::class);

        $hydrator = new MembreHydrator();
        $hydrator->setQualiteService($qualiteService);

        return $hydrator;
    }
}