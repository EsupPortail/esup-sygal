<?php

namespace Application\Form;

use Application\Entity\Db\RdvBu;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class RdvBuTheseDoctorantForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new RdvBu());

        $this->add((new Textarea('coordDoctorant'))
            ->setLabel("Téléphone :")
        );

        $this->add((new Textarea('dispoDoctorant'))
            ->setLabel("Disponibilités :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'coordDoctorant' => [
                'name' => 'coordDoctorant',
                'required' => true,
            ],
            'dispoDoctorant' => [
                'name' => 'dispoDoctorant',
                'required' => true,
            ],
        ]));
    }
}