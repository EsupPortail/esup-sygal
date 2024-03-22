<?php

namespace These\Form\DomaineHalSaisie\Fieldset;

use Application\Service\DomaineHal\DomaineHalService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class DomaineHalHydratorFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : DomaineHalHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        $domaineHalService = $container->get(DomaineHalService::class);

        $hydrator = new DomaineHalHydrator($entityManager);
        $hydrator->setDomaineHalService($domaineHalService);
        return $hydrator;
    }
}