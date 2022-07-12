<?php

namespace Formation\Form\SessionStructureValide;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Structure\StructureService;

class SessionStructureValideFormFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureValideForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureValideForm
    {
        /**
         * @var StructureService $structureService
         */
        $structureService = $container->get(StructureService::class);

        /**
         * @var  SessionStructureValideHydrator $hydrator
         */
        $hydrator = $container->get('HydratorManager')->get(SessionStructureValideHydrator::class);

        $form = new SessionStructureValideForm();
        $form->setStructureService($structureService);
        $form->setHydrator($hydrator);
        return $form;
    }
}