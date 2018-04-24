<?php

namespace Application\View\Helper;

use Application\Entity\Db\Structure;
use Application\Entity\Db\StructureConcreteInterface;

class StructureSubstitHelper extends AbstractHelper {
    /**
     * @return string
     */
    public function render()
    {
        // TODO: Implement render() method.
    }

    //TODO ajouter les different cas i.e. Etab, UR et ED

    /**
     * @param StructureConcreteInterface $structure
     * @return string
     */
    public function structureSource(StructureConcreteInterface $structure)
    {
        $texte =    "";
        $texte .=   '<div class="panel panel-warning">';

        $texte .=   '    <div class="panel-heading">';
        $texte .= $structure->getSource() ."/". $structure->getId() . " " . $structure->getId() ;

        $texte .=   '    </div>';

        $texte .=   '    <div class="panel-body">';
        $texte .=   '   <div class="" id="structure_'.$structure->getId().'">';
        $texte .=   '<input type="hidden" name="sourceIds[]" value="'.$structure->getStructure()->getId().'"/>';

        $texte .= "<div id='logo-div' class='pull-right '>";
        $texte .= "<p>";
        if ($structure->getCheminLogo() !== null) {
            $texte .= '<input champ="logo" id="logo_' . $structure->getSigle() .'" type="radio" name="logo" /> &nbsp; ';
        }
        $texte .= "<img class='current' style='max-width: 125px; max-height: 125px; border: 1px solid black; background-color: white;' src='data:image/png;base64," . base64_encode($structure->getLogoContent()) ."'/>";
        $texte .= '<input class="path" type="hidden" champ="cheminLogo" name="cheminLogo" value="'.$structure->getStructure()->getCheminLogo().'"/>';
        $texte .= "</div>";


        $texte .=   "<br/>";

        $texte .= "<span>";
        $texte .= "<input champ='sigle' id='sigle_".$structure->getSigle()."' type='radio' name='sigle'/> &nbsp; ";
        $texte .= "<span class='texte'>" . $structure->getSigle() ."</span>";
        $texte .= "</span>";

        $texte .=   "<br/>";

        $texte .= "<span>";
        $texte .= "<input champ='libelle' id='libelle_".$structure->getSigle()."' type='radio' name='libelle'/> &nbsp; ";
        $texte .= "<span class='texte'>" . $structure->getLibelle() . "</span>";
        $texte .= "</span>";
        $texte .= "</p>";


        $texte .=   "<button class='btn btn-danger supprimer'><span class='glyphicon glyphicon-remove'></span>Retirer de la substitution</button> ";
        $texte .=   "</div>";
        $texte .=   "</div>";
        $texte .=   "</div>";
        return $texte;

    }

    /**
     * @param Structure $structure
     * @return string
     */
    function structureCible(Structure $structure) {
        $texte =    "";
        $texte .=   '<div class="panel panel-success">';

        $texte .=   '    <div class="panel-heading">';
        $texte .= $structure->getSource() ."/". $structure->getId() . " " . $structure->getId() ;
        $texte .=   '    </div>';

        $texte .=   '    <div class="panel-body">';
        $texte .= "<div id='logo-div' class='pull-right'>";
        $texte .= "<input type='hidden' name='cible[cheminLogo]' id='logo' value='".$structure->getCheminLogo()."'/>";
        $texte .= "<img id='logo_tmp' style='max-width: 125px; max-height: 125px; border: 1px solid black; background-color: white;' src='data:image/png;base64," . base64_encode($structure->getLogoContent()) ."'/>";
        $texte .= "</div>";


        $texte .=   "<br/>";

        $texte .= "<input type='text' name='cible[sigle]' id='sigle' value='".$structure->getSigle()."'/>";
        $texte .=   "<br/>";

        $texte .= "<input type='text' name='cible[libelle]' id='libelle' value='".$structure->getLibelle()."'/>";

        $texte .=   "</div>";
        $texte .=   "</div>";
        $texte .=   "</div>";
        return $texte;
    }
}