<?php

namespace Structure\View\Helper;

use Application\View\Helper\AbstractHelper;
use Structure\Entity\Db\Structure;
use Structure\Entity\Db\StructureConcreteInterface;

class StructureSubstitHelper extends AbstractHelper
{
    /**
     * @return string
     */
    public function render()
    {
    }

    /**
     * @param StructureConcreteInterface $structurestructureConcrete
     * @param string|null                     $logoContent
     * @return string
     */
    public function structureSource(StructureConcreteInterface $structurestructureConcrete, ?string $logoContent): string
    {
        // structure substituée SANS REMONTER À LA STRUCTURE SUBSTITUANTE
        $structure = $structurestructureConcrete->getStructure(false);

        $texte = '<div class="card" data-struct-id="'.$structurestructureConcrete->getId().'" id="panel_' . $structurestructureConcrete->getId() . '" >';
        $texte .= '<div class="card-header bg-secondary text-white">';
        $texte .= 'Source : ' . $structurestructureConcrete->getSource() . " / Structure abstraite " . $structure->getId() . " / Structure concrète " . $structurestructureConcrete->getId();
        $texte .= '</div>';

        $texte .= '<div class="card-body">';

        $texte .= '<div class="float-start">';
        $texte .= '<input type="hidden" id="source" name="sourceIds[]" value="' . $structure->getId() . '" />';
        $texte .= '<table>';
        $texte .= '<tr>';
        $texte .= '<td><input champ="sigle" id="sigle_' . $structurestructureConcrete->getStructure(false)->getSigle() . '" type="radio" name="sigle"/> </td>';
        $texte .= '<th> &nbsp; Sigle : &nbsp; </th>';
        $texte .= '<td>' . $structurestructureConcrete->getStructure(false)->getSigle() . '</td>';
        $texte .= '</tr>';
        $texte .= '<tr>';
        $texte .= '<td><input champ="libelle" id="sigle_' . $structurestructureConcrete->getStructure(false)->getSigle() . '" type="radio" name="libelle"/> </td>';
        $texte .= '<th> &nbsp; Libellé : &nbsp; </th>';
        $texte .= '<td>' . $structurestructureConcrete->getStructure(false)->getLibelle() . '</td>';
        $texte .= '</tr>';
        $texte .= '<tr>';
        $texte .= '<td><input champ="code" id="sigle_' . $structurestructureConcrete->getStructure(false)->getSigle() . '" type="radio" name="code"/> </td>';
        $texte .= '<th> &nbsp; Code : &nbsp; </th>';
        $texte .= '<td>' . $structure->getCode() . '</td>';
        $texte .= '</tr>';
        $texte .= '</table>';

        $texte .= '<br/>';

        $texte .= '<button type="button" class="btn btn-danger btn-sm supprimer">';
        $texte .= '<span class="icon icon-remove"></span>';
        $texte .= 'Retirer de la substitution';
        $texte .= '</button>';
        $texte .= '</div>';

        $texte .= '<div id="logo-div" class="float-end ">';
        if ($structurestructureConcrete->getStructure(false)->getCheminLogo() !== null)
            $texte .= '<input champ="logo" id="logo_' . $structurestructureConcrete->getStructure(false)->getSigle() . '" type="radio" name="logo" /> &nbsp; ';
        $texte .= '<img class="current" style="max-width: 125px; max-height: 125px; border: 1px solid black; background-color: white;" src="data:image/*;base64,' . base64_encode($logoContent) . '"/>';
        $texte .= '<input class="path" type="hidden" champ="cheminLogo" name="cheminLogo" value="' . $structure->getCheminLogo() . '"/>';
        $texte .= '</div>';
        $texte .= '</div>';
        $texte .= '</div>';

        return $texte;

    }

    /**
     * @param Structure $structure
     * @param string|null    $logoContent
     * @return string
     */
    function structureCible(Structure $structure, ?string $logoContent): string
    {
        $texte = "";
        $texte .= '<div class="card">';

        $texte .= '    <div class="card-header bg-success">';
        $texte .= 'Source : ' . $structure->getSource() . " / Identifiant Structure : " . $structure->getId();
        $texte .= '    </div>';

        $texte .= '    <div class="card-body">';
        $texte .= "<div id='logo-div' class='float-end'>";
        $texte .= "<input type='hidden' name='cible[cheminLogo]' id='logo' value='" . $structure->getCheminLogo() . "'/>";
        $texte .= "<img id='logo_tmp' style='max-width: 125px; max-height: 125px; border: 1px solid black; background-color: white;' src='data:image/*;base64," . base64_encode($logoContent) . "'/>";
        $texte .= "</div>";

        $texte .= "<br/>";

        $texte .= '<table>';
        $texte .= '    <tr>';
        $texte .= '        <th> Sigle  </th>';
        $texte .= '        <td><input type="text" name="cible[sigle]" id="sigle" value="' . $structure->getSigle() . '"/></td>';
        $texte .= '    </tr>';
        $texte .= '    <tr>';
        $texte .= '        <th> Libellé  &nbsp; </th>';
        $texte .= '        <td><input type="text" name="cible[libelle]" id="libelle" value="' . $structure->getLibelle() . '"/></td>';
        $texte .= '    </tr>';
        $texte .= '    <tr>';
        $texte .= '        <th> Code  </th>';
        $texte .= '        <td><input type="text" name="cible[code]" id="code" value="' . $structure->getCode() . '"/></td>';
        $texte .= '    </tr>';
        $texte .= '</table>';

        $texte .= "</div>";
        $texte .= "</div>";
        $texte .= "</div>";

        return $texte;
    }
}