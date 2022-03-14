<?php

namespace Application\Assertion;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\ControllerAssertionInterface;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\RouteMatch;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Class BaseAssertion
 *
 * @package Application\Assertion
 * @method UserContextService getServiceUserContext()
 */
abstract class BaseAssertion extends AbstractAssertion
    implements UserContextServiceAwareInterface, MessageCollectorAwareInterface
{
    use UserContextServiceAwareTrait;
    use MessageCollectorAwareTrait;

    /**
     * @var ControllerAssertionInterface
     */
    protected $controllerAssertion;

    /**
     * @var PageAssertionInterface
     */
    protected $pageAssertion;

    /**
     * @var EntityAssertionInterface
     */
    protected $entityAssertion;

    /**
     * @param array $page
     * @return bool
     */
    public function __invoke(array $page)
    {
        return $this->assertPage($page);
    }

    /**
     * @param array $page
     * @return bool
     */
    private function assertPage(array $page)
    {
        if ($this->getRouteMatch() === null) {
            return false;
        }

        if ($this->pageAssertion === null) {
            return true;
        }

        $this->initPageAssertion();

        try {
            return $this->pageAssertion->assert($page);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }
    }

    /**
     * @param string $controller
     * @param null   $action
     * @param null   $privilege
     * @return bool
     */
    protected function assertController($controller, $action = null, $privilege = null)
    {
        if (! parent::assertController($controller, $action, $privilege)) {
            return false;
        }

        if ($this->controllerAssertion === null) {
            return true;
        }

        if ($this->getRouteMatch() === null) {
            return false;
        }

        $this->controllerAssertion->setContext([
            'controller' => $controller,
            'action' => $action,
        ]);
        $this->initControllerAssertion();

        try {
            return $this->controllerAssertion->assert(/*$controller, $action, */$privilege);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }
    }

    /**
     * @param ResourceInterface $entity
     * @param string            $privilege
     *
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null)
    {
        if (! parent::assertEntity($entity, $privilege)) {
            return false;
        }

        if ($this->entityAssertion === null) {
            return true;
        }

        if ($this->getRouteMatch() === null) {
            return false;
        }

        $this->initEntityAssertion($entity);

        try {
            return $this->entityAssertion->assert($privilege);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }
    }

    /**
     * @return static
     */
    abstract protected function initControllerAssertion();

    /**
     * @return static
     */
    abstract protected function initPageAssertion();

    /**
     * @param ResourceInterface $entity
     * @return static
     */
    abstract protected function initEntityAssertion(ResourceInterface $entity);

    /**
     * @param ControllerAssertionInterface $controllerAssertion
     * @return static
     */
    public function setControllerAssertion($controllerAssertion)
    {
        $this->controllerAssertion = $controllerAssertion;

        return $this;
    }

    /**
     * @param PageAssertionInterface $pageAssertion
     * @return static
     */
    public function setPageAssertion($pageAssertion)
    {
        $this->pageAssertion = $pageAssertion;

        return $this;
    }

    /**
     * @param EntityAssertionInterface $entityAssertion
     * @return static
     */
    public function setEntityAssertion($entityAssertion)
    {
        $this->entityAssertion = $entityAssertion;

        return $this;
    }

    /**
     * @return RouteMatch
     */
    protected function getRouteMatch()
    {
        /** @var RouteMatch $rm */
        $rm = $this->getMvcEvent()->getRouteMatch();

        return $rm;
    }
}