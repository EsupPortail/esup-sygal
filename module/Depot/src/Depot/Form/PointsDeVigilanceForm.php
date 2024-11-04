<?php

namespace Depot\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class PointsDeVigilanceForm extends Form
{

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->add((new Textarea('vigilance'))
            ->setLabel("Points de vigilance :")
        );

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'vigilance' => [
                'name' => 'vigilance',
                'required' => false,
            ],
        ]));
    }
}