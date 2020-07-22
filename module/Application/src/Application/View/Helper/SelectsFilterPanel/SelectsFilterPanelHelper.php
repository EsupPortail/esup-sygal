<?php

namespace Application\View\Helper\SelectsFilterPanel;

use Application\Service\These\Filter\TheseSelectFilter;
use Application\View\Renderer\PhpRenderer;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Resolver\TemplatePathStack;

class SelectsFilterPanelHelper extends AbstractHelper
{
    /**
     * @param TheseSelectFilter[] $filters
     * @return string
     */
    function __invoke(array $filters)
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();

        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('filter-form', ['filters' => $filters]);
    }

    /**
     * Retourne true si, d'après les valeurs des paramètres GET, l'<option> d'un <select> est sélectionnée.
     *
     * @param string   $optionName  Name de l'<option>
     * @param mixed    $optionValue Valeur de l'<option>
     * @param string[] $queryParams valeurs des paramètres GET
     * @return bool
     */
    static public function isSelectOptionActive($optionName, $optionValue, $queryParams)
    {
        return
            ($optionValue !== '' && ((isset($queryParams[$optionName]) && $queryParams[$optionName] === $optionValue))) ||
            ($optionValue === '' && (!isset($queryParams[$optionName]) || $queryParams[$optionName] === ''));
    }
}