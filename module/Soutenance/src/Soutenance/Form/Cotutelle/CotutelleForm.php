<?php

namespace Soutenance\Form\Cotutelle;

use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class CotutelleForm extends Form {

    public function init()
    {
        $this->add(
            (new Text('etablissement'))
                ->setLabel("Établissement de cotutelle :")
        );

        $this->add(
            (new Text('pays'))
                ->setLabel("Pays de cotutelle (ne pas renseigner pour la France) :")
        );


        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}