<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\MailConfirmationHydrator;
use Interop\Container\ContainerInterface;

class MailConfirmationHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container)
    {
        $individuService = $container->get(IndividuService::class);

        $hydrator = new MailConfirmationHydrator($container->get('doctrine.entitymanager.orm_default'));
        $hydrator->setIndividuService($individuService);

        return $hydrator;
    }
}
