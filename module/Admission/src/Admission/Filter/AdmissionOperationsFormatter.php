<?php

namespace Admission\Filter;

use Laminas\Filter\AbstractFilter;

class AdmissionOperationsFormatter extends AbstractFilter {

    public function filter($value)
    {
        // TODO: Implement filter() method.
    }

    public function htmlifyOperations(array $operations){
        $str = "<table>
                    <tr>";
        foreach ($operations["header"] as $headerLibelle) {
            $str .= "<th>" . $headerLibelle . "</th>";
        }
        $str .= "<tr>";
        foreach ($operations["operations"] as $operation) {
            $str .= "
                    <tr>
                        <th>" . $operation["libelle"] . "</th>";
            $str .= $operation["valeur"] ? "<th>" . $operation["valeur"] . "</th>" : "<th class='pas_valeur_avis_renseigne'>" . $operation["valeur"] . "</th>";
            $str .="    <th>" . $operation["createur"] . "</th>
                        <th>" . $operation["dateCreation"] . "</th>
                    </tr>
                ";
        }
        $str .= "</table>";
        return $str;
    }
}