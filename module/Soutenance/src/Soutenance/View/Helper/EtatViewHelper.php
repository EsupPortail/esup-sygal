<?php

namespace Soutenance\View\Helper;

use Soutenance\Entity\Etat;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class EtatViewHelper extends AbstractHelper
{
    /**
     * @param Etat $etat
     * @param array $options
     * @return string
     */
    public function render($etat, $options = []) {

        /** @var PhpRenderer $view */
        $view = $this->view;
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));
        $texte = $view->partial('etat', ['etat' => $etat, 'options' => $options]);
        return $texte;
    }
}