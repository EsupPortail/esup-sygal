<?php

namespace Application\Authentication\Adapter;

use UnicaenApp\Exception;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of AbstractFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class AbstractFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return strpos($requestedName, __NAMESPACE__) === 0 && class_exists($requestedName);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        switch ($requestedName) {
            case __NAMESPACE__ . '\Shib':
                $adapter = new Shib();
                break;
            case __NAMESPACE__ . '\Ldap':
                $adapter = new Ldap();
                break;
            default:
                throw new Exception\RuntimeException("Service demandé inattendu : '$requestedName'!");
                break;
        }

//        if ($adapter instanceof EventManagerAwareInterface) {
//            /** @var EventManager $eventManager */
//            $eventManager = $serviceLocator->get('event_manager');
//            $adapter->setEventManager($eventManager);
//            /* @var $userService \UnicaenAuth\Service\User */
//            $userService = $serviceLocator->get('unicaen-auth_user_service');
//            $eventManager->attach('userAuthenticated', [$userService, 'userAuthenticated'], 100);
//        }

        return $adapter;
    }
}