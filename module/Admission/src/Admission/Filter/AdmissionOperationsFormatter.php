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
                        <th>" . $operation["libelle"] . "</th>
                        <th>" . $operation["createur"] . "</th>
                        <th>" . $operation["dateCreation"] . "</th>
                    </tr>
                ";
        }
        $str .= "</table>";
        return $str;
    }
}