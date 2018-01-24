<?php

namespace Application\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Zend\View\Helper\AbstractHelper as ZFAbstractHelper;

abstract class AbstractHelper extends ZFAbstractHelper
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     * @return AbstractHelper|string
     */
    function __invoke($value = null)
    {
        if ($value === null) {
            return $this;
        }

        $this->value = $value;

        return $this->render();
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get the view object
     *
     * @return PhpRenderer
     */
    public function getView()
    {
        /** @var PhpRenderer $view */
        $view = parent::getView();

        return $view;
    }

    /**
     * @return string
     */
    abstract public function render();
}