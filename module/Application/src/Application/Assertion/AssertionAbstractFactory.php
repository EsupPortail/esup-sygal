<?php

namespace Application\Assertion;

use Application\Assertion\Interfaces\ControllerAssertionInterface;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use UnicaenApp\Service\MessageCollector;
use UnicaenAuthentification\Service\UserContext;
use UnicaenPrivilege\Service\AuthorizeService;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

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
    protected string $namespace = __NAMESPACE__;
    protected string $prefix = 'Assertion';

    protected array $requestedNameParts;
    protected string $requestedDomain;

    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        $this->requestedNameParts = explode('\\', $requestedName);

        $firstPart = $this->requestedNameParts[0] ?: null;
        if ($firstPart !== $this->prefix) {
            return false;
        }

        $this->requestedDomain = $this->requestedNameParts[1];

        return $this->isBaseAssertionRequested() || $this->isSpecializedAssertionRequested();
    }

    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return \Application\Assertion\BaseAssertion
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($this->isBaseAssertionRequested()) {
            $className = $this->computeBaseAssertionClassName();
            $prefix = $this->prefix . '\\' . $this->requestedDomain . '\\';
            /** @var BaseAssertion $instance */
            $instance = new $className;
            $this->initBaseAssertion($instance, $container, $prefix);
        } elseif ($this->isSpecializedAssertionRequested()) {
            $className = $this->computeSpecializedAssertionClassName();
            $instance = new $className;
        } else {
            throw new InvalidArgumentException("Assertion demandée inattendue : $requestedName");
        }

        return $instance;
    }

    private function isBaseAssertionRequested(): bool
    {
        return count($this->requestedNameParts) === 2;
    }

    private function isSpecializedAssertionRequested(): bool
    {
        return count($this->requestedNameParts) === 3;
    }

    protected function computeBaseAssertionClassName(): string
    {
        return $this->namespace . sprintf('\\%s\\%sAssertion', $this->requestedDomain, $this->requestedDomain);
    }

    protected function computeSpecializedAssertionClassName(): string
    {
        return $this->namespace . sprintf('\\%s\\%s%sAssertion', $this->requestedDomain, $this->requestedDomain, $this->requestedNameParts[2]);
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    private function initBaseAssertion(BaseAssertion $baseAssertion, ContainerInterface $container, string $prefix)
    {
        /* @var AuthorizeService $authorizeService */
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContext::class);

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
        $baseAssertion->setServiceAuthorize($authorizeService);

//        $logger = new Logger();
//        $logger->addWriter(new \Laminas\Log\Writer\Stream('/tmp/TheseEntityAssertion.log'));
//        $entityAssertion->setLogger($logger);
    }
}