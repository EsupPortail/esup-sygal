<?php

namespace Formation\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Resolver\TemplatePathStack;

class TypeViewHelper extends AbstractHelper
{
    /**
     * @param HasTypeInterface $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke(HasTypeInterface $object, array $options = [])
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('type', ['objet' => $object, 'options' => $options]);
    }
}