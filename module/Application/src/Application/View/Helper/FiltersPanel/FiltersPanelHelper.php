<?php

namespace Application\View\Helper\FiltersPanel;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\View\Renderer\PhpRenderer;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

class FiltersPanelHelper extends AbstractHelper
{
    /**
     * @param SearchFilter[] $filters
     * @return string
     */
    function __invoke(array $filters): string
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
     * @deprecated Utiliser à la place {@see SelectSearchFilter::isSelectOptionActive()}
     */
    static public function isSelectOptionActive($optionName, $optionValue, $queryParams)
    {
        return
            ($optionValue !== '' && ((isset($queryParams[$optionName]) && $queryParams[$optionName] === $optionValue))) ||
            ($optionValue === '' && (!isset($queryParams[$optionName]) || $queryParams[$optionName] === ''));
    }
}