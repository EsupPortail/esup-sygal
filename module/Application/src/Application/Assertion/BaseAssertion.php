<?php

namespace Application\Assertion;

use Application\Assertion\Exception\FailedAssertionException;
use Application\Assertion\Interfaces\ControllerAssertionInterface;
use Application\Assertion\Interfaces\EntityAssertionInterface;
use Application\Assertion\Interfaces\PageAssertionInterface;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use UnicaenApp\Service\MessageCollectorAwareInterface;
use UnicaenApp\Service\MessageCollectorAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

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

        $this->initControllerAssertion();

        try {
            return $this->controllerAssertion->assert($controller, $action, $privilege);
        } catch (FailedAssertionException $e) {
            if ($e->getMessage()) {
                $this->getServiceMessageCollector()->addMessage($e->getMessage(), __CLASS__);
            }
            return false;
        }
    }

    /**
     * @param ResourceInterface  $these
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $these, $privilege = null)
    {
        if (! parent::assertEntity($these, $privilege)) {
            return false;
        }

        if ($this->entityAssertion === null) {
            return true;
        }

        $this->initEntityAssertion();

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
     * @return static
     */
    abstract protected function initEntityAssertion();

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
}