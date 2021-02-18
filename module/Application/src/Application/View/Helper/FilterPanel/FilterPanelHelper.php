<?php

namespace Application\View\Helper\FilterPanel;

use Application\View\Helper\FiltersPanel\FiltersPanelHelper;
use Application\View\Renderer\PhpRenderer;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Class FilterPanelHelper
 * @package Application\View\Helper\FilterPanel
 * @deprecated Utiliser à la place {@see FiltersPanelHelper}
 */
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