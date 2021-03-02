<?php

namespace ComiteSuivi\View\Helper;

use Zend\View\Helper\AbstractHtmlElement;
use Zend\View\Helper\Partial;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

class AnneeTheseViewHelper extends AbstractHtmlElement {

    /**
     * @param integer $annee
     * @param array $options
     * @return Partial
     */
    public function __invoke(int $annee, $options = [])
    {
        $anneeTexte = self::render($annee);

        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));
        return $view->partial('annee-these', ['annee' => $anneeTexte]);
    }

    /**
     * @param integer $annee
     * @return string
     */
    static public function render(int $annee)
    {
        $anneeTexte = "<span style='color:darkred;'>Année non définie</span>";
        switch ($annee) {
            case 1 : $anneeTexte = "Première année"; break;
            case 2 : $anneeTexte = "Deuxième année"; break;
            case 3 : $anneeTexte = "Troisième année"; break;
            case 4 : $anneeTexte = "Quatrième année"; break;
            case 5 : $anneeTexte = "Cinquième année"; break;
            case 6 : $anneeTexte = "Sixième année"; break;
        }
        return $anneeTexte;
    }

}