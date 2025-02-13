<?php

namespace Soutenance\View\Helper;

use Depot\Controller\Plugin\UrlFichierHDR;
use Depot\Controller\Plugin\UrlFichierThese;
use Soutenance\Entity\Justificatif;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;

class JustificatifViewHelper extends AbstractHelper
{
    /**
     * @param Justificatif $justificatif
     * @param UrlFichierThese|UrlFichierHDR $urlFichier
     * @param string $urlSuppressionJustificatif
     * @param bool $canGererDocument
     * @param array $options
     * @return string
     */
    public function render($justificatif, $urlFichier, $urlSuppressionJustificatif, $canGererDocument, $options = []) {

        /** @var PhpRenderer $view */
        $view = $this->view;
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));
        $texte = $view->partial('justificatif', [
            'justificatif' => $justificatif,
            'urlFichier' => $urlFichier,
            'urlSuppressionJustificatif' => $urlSuppressionJustificatif,
            'canGererDocument' => $canGererDocument,
            'options' => $options]);
        return $texte;
    }
}