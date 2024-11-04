<?php

namespace Information\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;

class FichierForm extends Form {

    public function init()
    {
        $this
            ->add((
            new File('chemin'))
                //->setLabel('Fichier à téléverser :')
            );
        FormUtils::addUploadButton($this);
    }
}