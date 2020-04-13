<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RdvBuHydrator;
use Interop\Container\ContainerInterface;

class RdvBuHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $rdvBuHydrator = new RdvBuHydrator($container->get('doctrine.entitymanager.orm_default'));
        $rdvBuHydrator->setFichierTheseService($container->get('FichierTheseService'));
       // $rdvBuHydrator->setEntityManager($container->get('doctrine.entitymanager.orm_default'));
        return $rdvBuHydrator;
    }
}
