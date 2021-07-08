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
            'id'                    => 'id',
            'Source Code'           => 'source_code',
            'Nom usuel'             => 'nom_usuel',
            'Nom Patronymique'      => 'nom_patronymique',
            'Prénom 1'              => 'prenom1',
            'Prénom 2'              => 'prenom2',
            'Prénom 3'              => 'prenom3',
            'Email'                 => 'email',
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
                $html .= '<td>';
//                if ($clef === 'source_code') $html .= '<a href="'.$this->getView()->url('these/identite', ['these' => $entry['id']], [], true).'">';
                $html .= $entry[$valeur] ;
//                if ($clef === 'source_code') $html .= '</a>';
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }
}