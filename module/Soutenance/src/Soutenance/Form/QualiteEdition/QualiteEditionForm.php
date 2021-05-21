<?php

namespace Soutenance\Form\QualiteEdition;

use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class QualiteEditionForm extends Form {

    public function init()
    {
        $this->add(
            (new Text('libelle'))
                ->setLabel("Libelle :")
        );

        $this->add(
            (new Radio('rang'))
                ->setLabel("Rang :")
                ->setValueOptions(['A' => 'A', 'B' => 'B']))
        ;

        $this->add(
            (new Radio('hdr'))
                ->setLabel("Possède une habilitation à diriger des recherches :")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        $this->add(
            (new Radio('emeritat'))
                ->setLabel("Possède un émeritat :")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        $this->add(
            (new Radio('justificatif'))
                ->setLabel("Nécessite un justificatif :")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );
    }
}