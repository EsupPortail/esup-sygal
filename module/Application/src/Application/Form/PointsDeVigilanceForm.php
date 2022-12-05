<?php

namespace Application\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Element\Submit;
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

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'vigilance' => [
                'name' => 'vigilance',
                'required' => false,
            ],
        ]));
    }
}