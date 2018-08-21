<?php

namespace Soutenance\Form\PersopassModifier;

use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class PersopassModifierForm extends Form {

    public function init()
    {
        $this->add(
            (new Text('persopass'))
                ->setLabel("Persopass :")
        );
        $this->add(
            (new Checkbox('nouveau'))
                ->setLabel("nouveau :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Modifier le persopass")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}