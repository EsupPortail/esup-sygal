<?php

namespace Application\Filter;

use Application\Entity\Db\These;
use Zend\Filter\AbstractFilter;


class TitreFormatter extends AbstractFilter {

    /** Format n array of acteurs
     * @param These $these
     * @return string le titre reformatté de la thèse
     */
    public function doFormat($these)
    {
//        $text = "";
//        if ($these instanceof These) $text = $these->getTitre();
//        else $text = $these;

        $result = str_replace(["₀","ε"],["«","»"], $these->getTitre());
        return $result;
    }

    public function filter($value)
    {
        // TODO: Implement filter() method.
        return null;
    }


}