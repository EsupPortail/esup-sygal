<?php

namespace Indicateur\View\Helper;

use Indicateur\Model\Indicateur;
use Zend\Form\View\Helper\AbstractHelper;

class CompletIndicateurIndividuHelper extends AbstractHelper
{
    /**
     * @param Indicateur $indicateur
     * @param array $data
     * @return string
     */
    public function render($indicateur, $data) {

        $rubriques = [
            'id'                    => 'ID',
            'Source Code'           => 'SOURCE_CODE',
            'Nom usuel'             => 'NOM_USUEL',
            'Nom Patronymique'      => 'NOM_PATRONYMIQUE',
            'Prénom 1'              => 'PRENOM1',
            'Prénom 2'              => 'PRENOM2',
            'Prénom 3'              => 'PRENOM3',
            'Email'                 => 'EMAIL',
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
                $html .= '<td>' . $entry[$valeur] . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }
}