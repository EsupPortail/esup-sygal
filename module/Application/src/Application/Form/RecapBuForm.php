<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Submit;
use Zend\InputFilter\Factory;

class RecapBuForm extends Form
{

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        //$this->setObject(new RdvBu());

        $this->add(
            (new Text('orcid'))
            ->setLabel("Identifiant ORCID :")
        );
        $this->add(
            (new Text('nnt'))
            ->setLabel("Numéro national de these :")
        );

        $this->add((new Textarea('vigilance'))
            ->setLabel("Points de vigilance :")
        );


        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'orcid' => [
                'name' => 'orcid',
                'required' => false,
            ],
            'nnt' => [
                'name' => 'nnt',
                'required' => false,
            ],
            'vigilance' => [
                'name' => 'vigilance',
                'required' => false,
            ],
        ]));
    }
}