<?php

namespace Horodatage\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Horodatage\Entity\Db\Horodatage;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

class HorodatageViewHelper extends AbstractHelper
{
    public function __invoke(Horodatage $horadatage, array $options = []) : string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        return $view->partial('horodatage', ['horodatage' => $horadatage, 'options' => $options]);
    }
}