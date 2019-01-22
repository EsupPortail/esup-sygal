<?php

namespace Information\Form;

use Zend\Form\Element\File;
use Zend\Form\Element\Submit;
use Zend\Form\Form;

class FichierForm extends Form {

    public function init()
    {
        $this
            ->add((
            new File('chemin'))
                //->setLabel('Fichier à téléverser :')
            );
        $this
            ->add((
            new Submit('televerser'))
                ->setValue("Téléverser")
                ->setAttribute('class', 'btn btn-primary')
            );
    }
}