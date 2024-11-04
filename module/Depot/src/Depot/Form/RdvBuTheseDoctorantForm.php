<?php

namespace Depot\Form;

use Application\Utils\FormUtils;
use Depot\Entity\Db\RdvBu;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

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

        FormUtils::addSaveButton($this);

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