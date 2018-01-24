<?php

namespace Application\View\Helper\FilterPanel;

use Application\View\Renderer\PhpRenderer;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Resolver\TemplatePathStack;

class FilterPanelHelper extends AbstractHelper
{
    /**
     * @param array $config
     * @return string
     */
    function __invoke(array $config)
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();

        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('filter-form', ['config' => $config]);
    }

}