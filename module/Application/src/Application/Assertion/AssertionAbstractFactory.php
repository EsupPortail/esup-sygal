<?php

namespace Application\Assertion;

use Application\Assertion\Interfaces\ControllerAssertionInterface;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use UnicaenApp\Service\MessageCollector;
use UnicaenAuth\Service\AuthorizeService;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\Exception\InvalidArgumentException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

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

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        if (!$parts || $parts[0] !== self::START) {
            return false;
        }

        return
            $this->isBaseAssertionRequested($requestedName) ||
            $this->isSpecializedAssertionRequested($requestedName);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $parts = explode('\\', $requestedName);
        $parts = array_slice($parts, 1);
        $domain = $parts[0];

        if ($this->isBaseAssertionRequested($requestedName)) {
            $className = __NAMESPACE__ . sprintf('\\%s\\%sAssertion', $domain, $domain);
            $prefix = self::START . '\\' . $domain . '\\';

            /** @var BaseAssertion $baseAssertion */
            $baseAssertion = new $className;
            $this->initBaseAssertion($baseAssertion, $container, $prefix);

            return $baseAssertion;

        } elseif ($this->isSpecializedAssertionRequested($requestedName)) {
            $className = __NAMESPACE__ . sprintf('\\%s\\%s%sAssertion', $domain, $domain, $parts[1]);

            return new $className;

        } else {
            throw new InvalidArgumentException("Assertion demandée inattendue : $requestedName");
        }
    }

    private function initBaseAssertion(BaseAssertion $baseAssertion, ContainerInterface $container, $prefix)
    {
        /* @var AuthorizeService $authorizeService */
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        /** @var UserContextService $userContextService */
        $userContextService = $container->get('UnicaenAuth\Service\UserContext');

        // les Assertions spécialisées sont injectées dans l'Assertion de base
        /** @var EntityAssertionInterface $entityAssertion */
        $entityAssertion = $container->get($prefix . 'Entity');
        $entityAssertion->setUserContextService($userContextService);
        /** @var ControllerAssertionInterface $controllerAssertion */
        $controllerAssertion = $container->get($prefix . 'Controller');
        $controllerAssertion->setUserContextService($userContextService);
        /** @var PageAssertionInterface $pageAssertion */
        $pageAssertion = $container->get($prefix . 'Page');
        $pageAssertion->setAuthorizeService($authorizeService);

        /** @var MvcEvent $mvcEvent */
        $mvcEvent = $container->get('Application')->getMvcEvent();

        /** @var MessageCollector $messageCollector */
        $messageCollector = $container->get('MessageCollector');

        $baseAssertion->setEntityAssertion($entityAssertion);
        $baseAssertion->setControllerAssertion($controllerAssertion);
        $baseAssertion->setPageAssertion($pageAssertion);
        $baseAssertion->setMvcEvent($mvcEvent);
        $baseAssertion->setServiceMessageCollector($messageCollector);

//        $logger = new Logger();
//        $logger->addWriter(new \Zend\Log\Writer\Stream('/tmp/TheseEntityAssertion.log'));
//        $entityAssertion->setLogger($logger);

        return $baseAssertion;
    }

    private function isBaseAssertionRequested($requestedName)
    {
        $parts = explode('\\', $requestedName);

        return count($parts) === 2;
    }

    private function isSpecializedAssertionRequested($requestedName)
    {
        $parts = explode('\\', $requestedName);

        return count($parts) === 3;
    }
}