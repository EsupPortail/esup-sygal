<?php

namespace Indicateur\View\Helper;

use Indicateur\Model\Indicateur;
use Zend\Form\View\Helper\AbstractHelper;

class ResumeIndicateurStructureHelper extends AbstractHelper
{
    /**
     * @param Indicateur $indicateur
     * @param array $data
     * @return string
     */
    public function render($indicateur, $data, $route = null, $limite = 5)
    {
        $url = $this->getView()->url('indicateur/view', ['indicateur' => $indicateur->getId()], [], true);

        $html  = '';
        $html .= '<div class="col-md-4">';
        $html .= '<div class="panel panel-'.$indicateur->getClass().'">';
            $html .= '<div class="panel-heading">';
                $html .= '<span class="glyphicon glyphicon-home"></span> &nbsp; ';
                $html .= $indicateur->getLibelle();
                $html .= ' <span class="badge">' . count($data) . '</span>';
            $html .= '</div>';
            $html .= '<div class="panel-body">';
                $html .= '<table class="table table-extra-condensed">';
                    $html .= '<thead>';
                        $html .= '<th> Id </th>';
                        $html .= '<th> Sigle </th>';
//                        $html .= '<th> Libelle </th>';
                        $html .= '<th> Type </th>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                    for($position = 0 ; $position < $limite && $position < count($data) ; $position++) {
                        $html .= '<tr>';
                            $html .= '<td>'.$data[$position]["ID"].'</td>';
                            $html .= '<td>'.$data[$position]["SIGLE"].'</td>';
//                            $html .= '<td>'.$data[$position]["LIBELLE"].'</td>';
                            $html .= '<td>';
                            switch($data[$position]["TYPE_STRUCTURE_ID"]) {
                                case 1 : $html .= "Etab"; break;
                                case 2 : $html .= "ED"; break;
                                case 3 : $html .= "UR"; break;
                            }
                            $html .= '</td>';
                        $html .= '</tr>';
                    }
                    for ( ; $position < $limite ; $position++) {
                        $html .= "<tr><td>&nbsp;</td></tr>";
                    }
                    $html .= '</tbody>';
                $html .= '</table>';

                $html .= '<a href="'.$url.'" class="btn btn-primary"> <span class="glyphicon glyphicon-eye-open"></span> Visualiser les données </a>';

            $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}