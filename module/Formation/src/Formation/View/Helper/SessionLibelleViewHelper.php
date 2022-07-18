<?php

namespace Formation\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Session;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Resolver\TemplatePathStack;

class SessionLibelleViewHelper extends AbstractHelper
{
    /**
     * @param Session|null $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke(?Session $object, array $options = ["index" => true, "module" => true])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('session-libelle', ['session' => $object, 'options' => $options]);
    }
}