<?php

namespace Application\Assertion;

use Application\Assertion\Interfaces\ControllerAssertionInterface;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\UserContextService;
use UnicaenAuth\Service\AuthorizeService;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\InvalidServiceNameException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Instancie l'Assertion de base correspondant au nom de service suivant :
 * - 'Assertion\\{Domain}'
 *
 * Ou l'Assertion spécialisée correspondant à l'un des noms de service suivants :
 * - 'Assertion\\{Domain}\\Entity'
 * - 'Assertion\\{Domain}\\Controller'
 * - 'Assertion\\{Domain}\\Page'
 *
 * @author Unicaen
 */
class AssertionAbstractFactory implements AbstractFactoryInterface
{
    const START = 'Assertion';

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        if (!$parts || $parts[0] !== self::START) {
            return false;
        }

        return
            $this->isBaseAssertionRequested($requestedName) ||
            $this->isSpecializedAssertionRequested($requestedName);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     * @return BaseAssertion|EntityAssertionInterface|ControllerAssertionInterface|PageAssertionInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        $parts = array_slice($parts, 1);
        $domain = $parts[0];

        if ($this->isBaseAssertionRequested($requestedName)) {
            $className = __NAMESPACE__ . sprintf('\\%s\\%sAssertion', $domain, $domain);
            $prefix = self::START . '\\' . $domain . '\\';

            /** @var BaseAssertion $baseAssertion */
            $baseAssertion = new $className;
            $this->initBaseAssertion($baseAssertion, $serviceLocator, $prefix);

            return $baseAssertion;

        } elseif ($this->isSpecializedAssertionRequested($requestedName)) {
            $className = __NAMESPACE__ . sprintf('\\%s\\%s%sAssertion', $domain, $domain, $parts[1]);

            return new $className;

        } else {
            throw new InvalidServiceNameException("Assertion demandée inattendue : $requestedName");
        }
    }


    private function initBaseAssertion(BaseAssertion $baseAssertion, ServiceLocatorInterface $serviceLocator, $prefix)
    {
        /* @var AuthorizeService $authorizeService */
        $authorizeService = $serviceLocator->get('BjyAuthorize\Service\Authorize');
        /** @var UserContextService $userContextService */
        $userContextService = $serviceLocator->get('UnicaenAuth\Service\UserContext');

        // les Assertions spécialisées sont injectées dans l'Assertion de base
        /** @var EntityAssertionInterface $entityAssertion */
        $entityAssertion = $serviceLocator->get($prefix . 'Entity');
        $entityAssertion->setUserContextService($userContextService);
        /** @var ControllerAssertionInterface $controllerAssertion */
        $controllerAssertion = $serviceLocator->get($prefix . 'Controller');
        $controllerAssertion->setUserContextService($userContextService);
        /** @var PageAssertionInterface $pageAssertion */
        $pageAssertion = $serviceLocator->get($prefix . 'Page');
        $pageAssertion->setAuthorizeService($authorizeService);

        $baseAssertion->setEntityAssertion($entityAssertion);
        $baseAssertion->setControllerAssertion($controllerAssertion);
        $baseAssertion->setPageAssertion($pageAssertion);

        return $baseAssertion;
    }


    private function isBaseAssertionRequested($requestedName)
    {
        $parts = explode('\\', $requestedName);
        $isBaseAssertion = count($parts) === 2;

        return $isBaseAssertion;
    }

    private function isSpecializedAssertionRequested($requestedName)
    {
        $parts = explode('\\', $requestedName);
        $isSpecializedAssertion = count($parts) === 3;

        return $isSpecializedAssertion;
    }
}