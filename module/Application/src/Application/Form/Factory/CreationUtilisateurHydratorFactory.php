<?php


namespace Application\Form\Factory;

use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Interop\Container\ContainerInterface;

class CreationUtilisateurHydratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new CreationUtilisateurHydrator();
    }
}