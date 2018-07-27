<?php

namespace Soutenance\Form\SoutenanceRefus;

use UnicaenApp\Form\Element\Date;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Time;
use Zend\Form\Form;

class SoutenanceRefusForm extends Form {

    public function init()
    {
        $this->add(
            (new Textarea('motif'))
                ->setLabel("Motif du refus de la proposition de soutenance :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Refuser la proposition")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}