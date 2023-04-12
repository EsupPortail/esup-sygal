<?php

namespace Horodatage\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Horodatage\Entity\Interfaces\HasHorodatagesInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

class DernierHorodatageViewHelper extends AbstractHelper
{
    public function __invoke(HasHorodatagesInterface $element, string $type, ?string $complement = null, array $options = []) : string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        $horodatage = $element->getLastHoradatage($type,$complement);
        return $view->partial('dernier-horodatage', ['horodatage' => $horodatage, 'options' => $options]);
    }
}