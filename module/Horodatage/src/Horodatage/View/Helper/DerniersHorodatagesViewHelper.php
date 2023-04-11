<?php

namespace Horodatage\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Horodatage\Entity\Interfaces\HasHorodatagesInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

/** $types est une table de couples (type, complement)*/
class DerniersHorodatagesViewHelper extends AbstractHelper
{
    public function __invoke(HasHorodatagesInterface $element, array $types, array $options = []) : string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        $horodatages = [];
        foreach ($types as $couple) {
            [$type, $complement] = $couple;
            $horodatage = $element->getLastHoradatage($type, $complement);
            if ($horodatage !== null) $horodatages[] = $horodatage;
        }
        return $view->partial('derniers-horodatages', ['horodatages' => $horodatages, 'options' => $options]);
    }
}