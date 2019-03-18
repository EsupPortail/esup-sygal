<?php

namespace Indicateur\View\Helper;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\Structure\StructureService;
use Indicateur\Model\Indicateur;
use Zend\Form\View\Helper\AbstractHelper;

class CompletIndicateurTheseHelper extends AbstractHelper
{
    /**
     * @param Indicateur $indicateur
     * @param array $data
     * @param StructureService $structureService
     * @return string
     */
    public function render($indicateur, $data, $structureService) {

        $rubriques = [
            'id'                    => 'ID',
            'Source Code'           => 'SOURCE_CODE',
            'Titre'                 => 'TITRE',
            'Première inscription'  => 'DATE_PREM_INSC',
            'Date de soutenance'    => 'DATE_SOUTENANCE',
            'Établissement d\'inscription'         => 'ETABLISSEMENT_ID',
            'École doctorale'       => 'ECOLE_DOCT_ID',
            'Unité de recherche'       => 'UNITE_RECH_ID',
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
                    switch($clef) {
                        case 'Titre':
                            $html .= '<a href="' . $this->getView()->url('these/identite', ['these' => $entry['ID']], [], true) . '">';
                            $html .= $entry[$valeur];
                            $html .= '</a>';
                            break;
                        case 'Établissement d\'inscription':
                            if ($entry[$valeur]) {
                                /** @var Etablissement $etablissement */
                                $etablissement = $structureService->getStructuresConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_ETABLISSEMENT, $entry[$valeur]);
                                $html .= '<abbr title="' . $etablissement->getLibelle() . '">' . $etablissement->getSigle() . '</abbr>';
                            } else $html .= "---";
                            break;
                        case 'École doctorale':
                            if ($entry[$valeur]) {
                                /** @var EcoleDoctorale $ecole */
                                $ecole = $structureService->getStructuresConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_ECOLE_DOCTORALE, $entry[$valeur]);
                                $html .= '<abbr title="' . $ecole->getLibelle() . '">' . $ecole->getSigle() . '</abbr>';
                            } else {
                                $html .= "---";
                            }
                            break;
                        case 'Unité de recherche':
                            if ($entry[$valeur]) {
                                /** @var UniteRecherche $unite */
                                $unite = $structureService->getStructuresConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_UNITE_RECHERCHE, $entry[$valeur]);
                                $html .= '<abbr title="' . $unite->getLibelle() . '">' . $unite->getSigle() . '</abbr>';
                            } else {
                                $html .= "---";
                            }
                            break;
                        default:
                            $html .= $entry[$valeur];
                            break;
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }
}