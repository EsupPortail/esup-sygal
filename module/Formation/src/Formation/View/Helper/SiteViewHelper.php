<?php

namespace Formation\View\Helper;

use Structure\Entity\Db\Etablissement;
use Application\View\Renderer\PhpRenderer;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\Partial;
use Laminas\View\Resolver\TemplatePathStack;

class SiteViewHelper extends AbstractHelper
{
    /**
     * @param HasSiteInterface|Etablissement $object
     * @param array $options
     * @desc $options['long'] si true le site est affiché avec le libellé et avec le sigle sinon
     * @return string|Partial
     */
    public function __invoke($object, array $options = [])
    {
        $site = $object;
        if ($site instanceof HasSiteInterface) $site = $object->getSite();

        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('site', ['site' => $site, 'options' => $options]);
    }
}