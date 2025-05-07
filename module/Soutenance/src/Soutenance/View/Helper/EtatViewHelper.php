<?php

namespace Soutenance\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Soutenance\Entity\Etat;
use Laminas\View\Helper\AbstractHelper;
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
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));
        return $view->partial('etat', ['etat' => $etat, 'options' => $options]);
    }
}