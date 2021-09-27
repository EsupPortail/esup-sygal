<?php

namespace Formation\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Session;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Partial;
use Zend\View\Resolver\TemplatePathStack;

class SessionInscriptionViewHelper extends AbstractHelper
{
    /**
     * @param Session|null $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke(?Session $object, array $options = [])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('session-inscription', ['session' => $object, 'options' => $options]);
    }
}