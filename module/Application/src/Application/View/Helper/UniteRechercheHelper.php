<?php

namespace Application\View\Helper;

use Application\Entity\Db\UniteRecherche;

/**
 * Class UniteRechercheHelper
 *
 * @method string getLibelle()
 * @method string getSigle()
 * @method string getSourceCode()
 */
class UniteRechercheHelper extends AbstractHelper
{
    /**
     * @var UniteRecherche
     */
    protected $value;

    /**
     * @param UniteRecherche $value
     * @return $this
     */
    function __invoke($value = null)
    {
        $this->value = $value;

        return $this;
    }

    function __call($name, $arguments)
    {
        $attr = call_user_func_array([$this->value, $name], $arguments);

        return $this->format($attr);
    }

    private function format($text)
    {
        if ($this->value->estNonHistorise()) {
            return $text;
        }

        return sprintf('<span title="Unité de recherche supprimée (historisée)" class="historisee">%s</span>', $text);
    }

    /**
     * @param UniteRecherche $value
     * @return string
     */
    public function render($value = null)
    {
        return $this->format((string) ($value ? $value : $this->value));
    }
}