<?php

namespace RapportActivite\Event\Validation;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;

class RapportActiviteValidationEventListenerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationEventListener
    {
        /** @var \RapportActivite\Service\Avis\RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);

        $listener = new RapportActiviteValidationEventListener();
        $listener->setRapportActiviteAvisService($rapportActiviteAvisService);

        return $listener;
    }
}