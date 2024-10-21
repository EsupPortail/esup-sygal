<?php

namespace Horodatage\View\Helper;

use Application\View\Renderer\PhpRenderer;
use Horodatage\Entity\Interfaces\HasHorodatagesInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;

class HorodatagesParTypesViewHelper extends AbstractHelper
{
    public function __invoke(HasHorodatagesInterface $element, array $types, array $options = []) : string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        $horodatages = [];
        $hasMultipleEvenements = false;
        foreach ($types as $couple) {
            [$type, $complement] = $couple;
            $allHorodatages = $element->getHorodatages($type, $complement);
            if (!empty($allHorodatages)) {
                // Le dernier horodatage
                $lastHorodatage = $element->getLastHoradatage($type, $complement);

                $horodatages[$complement] = [
                    'last' => $lastHorodatage,
                    'others' => $allHorodatages, // Les autres horodatages (le reste du tableau)
                ];

                if (count($horodatages[$complement]['others']) > 1) {
                    $hasMultipleEvenements = true;
                }
            }
        }
        return $view->partial('horodatages-par-types', ['horodatages' => $horodatages, 'options' => $options, 'hasMultipleEvenements' => $hasMultipleEvenements]);
    }
}