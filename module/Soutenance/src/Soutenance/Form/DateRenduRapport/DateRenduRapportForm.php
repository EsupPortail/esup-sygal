<?php

namespace Soutenance\Form\DateRenduRapport;

use UnicaenApp\Form\Element\Date;
use Zend\Form\Element\Submit;
use Zend\Form\Form;

class DateRenduRapportForm extends Form {

    public function init()
    {
        $this->add(
            (new Date('date'))
                ->setLabel("Date de rendu des rapports :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

}