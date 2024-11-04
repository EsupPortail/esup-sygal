<?php

namespace Soutenance\Form\DateRenduRapport;

use Application\Utils\FormUtils;
use Laminas\Form\Form;
use UnicaenApp\Form\Element\Date;

class DateRenduRapportForm extends Form {

    public function init()
    {
        $this->add(
            (new Date('date'))
                ->setLabel("Date de rendu des rapports :")
        );

        FormUtils::addSaveButton($this);
    }

}