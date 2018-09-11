<?php

namespace Application\Assertion;

use Application\Assertion\Interfaces\ControllerAssertionInterface;

abstract class ControllerAssertion implements ControllerAssertionInterface
{
    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->controller = isset($context['controller']) ? $context['controller'] : null;
        $this->action     = isset($context['action']) ? $context['action'] : null;
    }
}