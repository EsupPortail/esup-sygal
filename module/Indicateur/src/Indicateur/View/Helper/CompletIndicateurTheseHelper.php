<?php

namespace Indicateur\View\Helper;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\Structure\StructureService;
use Indicateur\Model\Indicateur;
use Laminas\Form\View\Helper\AbstractHelper;

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
            'id'                    => 'id',
            'Source Code'           => 'source_code',
            'Titre'                 => 'titre',
            'Première inscription'  => 'date_prem_insc',
            'Date de soutenance'    => 'date_soutenance',
            'Établissement d\'inscription'         => 'etablissement_id',
            'École doctorale'       => 'ecole_doct_id',
            'Unité de recherche'       => 'unite_rech_id',
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
                            $html .= '<a href="' . $this->getView()->url('these/identite', ['these' => $entry['id']], [], true) . '">';
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