<?php

namespace Formation\View\Helper;

use Application\Entity\Db\Etablissement;
use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Partial;
use Zend\View\Resolver\TemplatePathStack;

class SiteViewHelper extends AbstractHelper
{
    /**
     * @param HasSiteInterface|Etablissement $object
     * @param array $options
     * @return string|Partial
     */
    public function __invoke($object, $options = [])
    {
        $site = $object;
        if ($site instanceof HasSiteInterface) $site = $object->getSite();

        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('site', ['site' => $site, 'options' => $options]);
    }
}