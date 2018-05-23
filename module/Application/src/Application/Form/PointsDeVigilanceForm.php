<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Submit;
use Zend\InputFilter\Factory;

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