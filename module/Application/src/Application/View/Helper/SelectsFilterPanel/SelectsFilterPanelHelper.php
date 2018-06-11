<?php

namespace Application\View\Helper\SelectsFilterPanel;

use Application\View\Renderer\PhpRenderer;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Resolver\TemplatePathStack;

class SelectsFilterPanelHelper extends AbstractHelper
{
    /**
     * @param array  $config
     * @param string $formAction
     * @return string
     */
    function __invoke(array $config, $formAction = null)
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();

        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('filter-form', ['config' => $config]);
    }

}