<?php

namespace Formation\View\Helper;

use Application\Entity\Db\Etablissement;
use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Partial;
use Zend\View\Resolver\TemplatePathStack;

class ModaliteViewHelper extends AbstractHelper
{
    /**
     * @param HasModaliteInterface $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke(HasModaliteInterface $object, $options = [])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('modalite', ['objet' => $object, 'options' => $options]);
    }
}