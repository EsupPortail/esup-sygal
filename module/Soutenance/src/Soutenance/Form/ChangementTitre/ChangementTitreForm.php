<?php

namespace Soutenance\Form\ChangementTitre;

use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;

class ChangementTitreForm extends Form {

    public function init()
    {
        $this->add(
            (new Textarea('titre'))
                ->setLabel("Nouveau titre :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}