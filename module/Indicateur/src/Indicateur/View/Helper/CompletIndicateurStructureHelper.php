<?php

namespace Indicateur\View\Helper;

use Indicateur\Model\Indicateur;
use Zend\Form\View\Helper\AbstractHelper;

class CompletIndicateurStructureHelper extends AbstractHelper
{
    /**
     * @param Indicateur $indicateur
     * @param array $data
     * @return string
     */
    public function render($indicateur, $data) {

        $rubriques = [
            'id'                    => 'ID',
            'Sigle'                 => 'SIGLE',
            'Libelle'               => 'LIBELLE',
            'Type'                  => 'TYPE_STRUCTURE_ID',
        ];

        $html  = '';
        $html .= '<table class="table table-extra-condensed">';
            $html .= '<thead>';
            $html .= '<tr>';
            foreach ($rubriques as $clef => $valeur) {
                $html .= '<th> '.$clef.' </th>';
            }
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            foreach($data as $entry) {
                $html .= '<tr>';
                foreach ($rubriques as $clef => $valeur) {
                    if ($clef === 'Type') {
                        switch($entry[$valeur]) {
                            case "1" : $html .= '<td>' . "Établissement" . '</td>'; break;
                            case "2" : $html .= '<td>' . "École doctorale" . '</td>'; break;
                            case "3" : $html .= '<td>' . "Unité de recherche" . '</td>'; break;
                            default : $html .= '<td>' . '<i>'.$entry[$valeur].'</i>' . '</td>'; break;
                        }
                    } else {
                        $html .= '<td>' . $entry[$valeur] . '</td>';
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }
}