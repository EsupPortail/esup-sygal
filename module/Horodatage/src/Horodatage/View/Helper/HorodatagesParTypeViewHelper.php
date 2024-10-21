<?php

namespace Horodatage\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Horodatage\Entity\Interfaces\HasHorodatagesInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

class HorodatagesParTypeViewHelper extends AbstractHelper
{
    public function __invoke(HasHorodatagesInterface $element, string $type, ?string $complement = null, array $options = []) : string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        $horodatages = $element->getHorodatages($type, $complement);
        return $view->partial('horodatages-par-type', ['element' => $element,'horodatages' => $horodatages, 'options' => $options]);
    }
}