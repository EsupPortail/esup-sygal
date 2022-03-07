<?php

namespace Formation\View\Helper;

use Application\Entity\Db\Etablissement;
use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Resolver\TemplatePathStack;

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