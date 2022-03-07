<?php

namespace Soutenance\View\Helper;

use Application\Controller\Plugin\UrlFichierThese;
use Soutenance\Entity\Justificatif;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class JustificatifViewHelper extends AbstractHelper
{
    /**
     * @param Justificatif $justificatif
     * @param UrlFichierThese $urlFichierThese
     * @param array $options
     * @return string
     */
    public function render($justificatif, $urlFichierThese, $options = []) {

        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));
        $texte = $view->partial('justificatif', ['justificatif' => $justificatif, 'urlFichierThese' => $urlFichierThese, 'options' => $options]);
        return $texte;
    }
}