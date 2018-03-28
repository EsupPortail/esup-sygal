<?php


namespace Application\Form\Factory;

use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class CreationUtilisateurHydratorFactory
{

    public function __invoke(HydratorPluginManager $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();
        //$parentLocator->get('doctrine.entitymanager.orm_default')
        $hydrator = new CreationUtilisateurHydrator();
        return $hydrator;
    }
}