<?php

namespace Soutenance\Form\ChangementTitre;

use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class ChangementTitreForm extends Form {

    public function init()
    {
        $this->add(
            (new Text('titre'))
                ->setLabel("Nouveau titre :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}