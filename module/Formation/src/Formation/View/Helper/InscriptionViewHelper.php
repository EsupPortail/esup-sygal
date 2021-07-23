<?php


namespace Formation\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Partial;
use Zend\View\Resolver\TemplatePathStack;

class InscriptionViewHelper extends AbstractHelper
{
    /**
     * @param Inscription $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke(Inscription $object, $options = [])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('inscription', ['inscription' => $object, 'options' => $options]);
    }
}