<?php

namespace Indicateur\View\Helper;

use Indicateur\Model\Indicateur;
use Zend\Form\View\Helper\AbstractHelper;

class CompletIndicateurTheseHelper extends AbstractHelper
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
            'Titre'                 => 'TITRE',
            'Première inscription'  => 'DATE_PREM_INSC',
            'Date de soutenance'    => 'DATE_SOUTENANCE',
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