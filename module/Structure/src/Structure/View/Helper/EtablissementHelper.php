<?php

namespace Structure\View\Helper;

use Application\View\Helper\AbstractHelper;
use Structure\Entity\Db\Etablissement;

/**
 * Class EcoleDoctoraleHelper
 *
 * @method string getLibelle()
 * @method string getSigle()
 * @method string getSourceCode()
 */
class EtablissementHelper extends AbstractHelper
{
    /**
     * @var Etablissement
     */
    protected $value;

    /**
     * @param Etablissement $value
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

        return sprintf('<span title="Établissement supprimé (historisée)" class="historise">%s</span>', $text);
        //return $text;
    }

    /**
     * @param Etablissement $value
     * @return string
     */
    public function render($value = null)
    {
        return $this->format((string) ($value ? $value : $this->value));
    }
}