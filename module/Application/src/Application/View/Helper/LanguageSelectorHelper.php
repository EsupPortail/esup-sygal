<?php

/**
 * @author Jean-Philippe Métivier <jean-philippe.metivier@unicaen.fr>
 * cette aide de vue fournit un affichage de la langue courante (\Locale::getDefault)
 * au clic elle ouvre une pop-over proposant les langues disponibles
 * la locale est ensuite changée au clic sur une des langues disponibles
 *
 * 20171221 :: v0 afficher la langue contenu dans \Locale::getDefault
 **/

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class LanguageSelectorHelper extends AbstractHelper
{

    public $textOnly = true;
    public $flagPath = "flags/";
    public $languages;

    public function __construct($languages)
    {
        $this->languages = $languages;
        if (\Locale::getDefault() == 'en_US_POSIX') \Locale::setDefault('fr_FR');
    }

    public function render()
    {
        $html = \Locale::getDefault();
        return $html;
    }



    public function __toString()
    {
        $languages ="";

        $id = "LanguageSelector";
        $title   = $this->getView()->translate("Choix de la langue");

        $content = "<ul>";
        foreach ($this->languages as $language) {
            $url   = $this->getView()->url($this->getView()->route,  ['language' => $language], [$this->getView()->queryParams()], true);
            $flag  = $this->flagPath.$language.".png";
            $label = "<img src=\"".$this->getView()->basePath($flag)."\" style=\"width:40px;\"/>";
            $content .= "<li><a href=\"".$url."\">".$label."</a></li><br/>";
        }
        $content .= "</ul>";

        $to_display  = "<a class='navbar-link' data-bs-placement='bottom' data-bs-toggle='popover' data-bs-html='true'  id='".$id."' title='".$title."' data-bs-content='".$content."' >";
        $flag  = $this->flagPath.\Locale::getDefault().".png";
        $to_display .= "<img src=\"".$this->getView()->basePath($flag)."\" style=\"width:40px;\"/>";
        $to_display .= "</a>";

        return $to_display;
    }
}