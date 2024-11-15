<?php

namespace Soutenance\Form\QualiteEdition;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;

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
                ->setLabel("Nécessite un justificatif (chercheur&middot;e étranger&middot;ère):")
                ->setValueOptions(['O' => 'Oui', 'N' => 'Non']))
        ;

        FormUtils::addSaveButton($this);
    }
}