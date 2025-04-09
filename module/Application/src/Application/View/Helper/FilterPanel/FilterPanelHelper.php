<?php

namespace Application\View\Helper\FilterPanel;

use Application\View\Renderer\PhpRenderer;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

/**
 * Aide de vue générant des filtres au format lien hypertexte
 *
 * @deprecated Utiliser {@see \UnicaenSearch\View\Helper\FiltersPanel\FiltersPanelHelper} à la place,
 * lorsqu'il existera la possibilité de dessiner un filtre (select) au format lien hypertexte.
 */
class FilterPanelHelper extends AbstractHelper
{
    /**
     * @param array $config
     * @return string
     */
    public function __invoke(array $config)
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();

        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('filter-form', ['config' => $config]);
    }

}