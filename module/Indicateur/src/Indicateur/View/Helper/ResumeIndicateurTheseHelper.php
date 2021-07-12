<?php

namespace Indicateur\View\Helper;

use Indicateur\Model\Indicateur;
use Laminas\Form\View\Helper\AbstractHelper;

class ResumeIndicateurTheseHelper extends AbstractHelper
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
                $html .= '<span class="glyphicon glyphicon-book"></span> &nbsp; ';
                $html .= $indicateur->getLibelle();
                $html .= ' <span class="badge">' . count($data) . '</span>';
            $html .= '</div>';
            $html .= '<div class="panel-body">';
                $html .= '<table class="table table-extra-condensed">';
                    $html .= '<thead>';
                        $html .= '<th> Thèse </th>';
                        $html .= '<th> État </th>';
                        $html .= '<th> Inscription </th>';
                        $html .= '<th> Soutenance </th>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                    for($position = 0 ; $position < $limite && $position < count($data) ; $position++) {
                        $html .= '<tr>';
                            $html .= '<td>'.$data[$position]["source_code"].'</td>';
                            $html .= '<td>'.$data[$position]["etat_these"].'</td>';
                            $html .= '<td>'.explode(" ",$data[$position]["date_prem_insc"])[0].'</td>';
                            $html .= '<td>'.explode(" ",$data[$position]["date_soutenance"])[0].'</td>';
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