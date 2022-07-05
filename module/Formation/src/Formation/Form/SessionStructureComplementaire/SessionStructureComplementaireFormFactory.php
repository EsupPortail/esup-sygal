<?php

namespace Formation\Form\SessionStructureComplementaire;

use Laminas\Form\Form;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Structure\StructureService;
use Structure\Service\Structure\StructureServiceAwareTrait;

class SessionStructureComplementaireFormFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureComplementaireForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureComplementaireForm
    {
        /**
         * @var StructureService $structureService
         */
        $structureService = $container->get(StructureService::class);

        /**
         * @var  SessionStructureComplementaireHydrator $hydrator
         */
        $hydrator = $container->get('HydratorManager')->get(SessionStructureComplementaireHydrator::class);

        $form = new SessionStructureComplementaireForm();
        $form->setStructureService($structureService);
        $form->setHydrator($hydrator);
        return $form;
    }
}