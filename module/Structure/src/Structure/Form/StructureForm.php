<?php

namespace Structure\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;

abstract class StructureForm extends Form
{
    /**
     * NB: hydrateur injecté par la factory
     */
    public function init(): void
    {
        $this->add(new Hidden('id'));

        $this->add((new Text('sigle'))
            ->setLabel("Sigle")
        );

        $this->add((new Text('libelle'))
            ->setLabel("Libellé")
        );

        $this->add((new Text('code'))
            ->setLabel("Code")
        );

        $this->add((new Text('id_ref'))
            ->setLabel("IdRef")
        );

        $this->add((new Text('id_hal'))
            ->setLabel("IdHAL")
        );

        $this->add((new File('cheminLogo'))
            ->setLabel('Logo :')
        );

        $this->add((new Submit('supprimer-logo'))
            ->setValue("Supprimer le logo")
            ->setAttribute('class', 'btn btn-danger')
        );

        $this->add(new Csrf('csrf'));

        FormUtils::addSaveButton($this);
    }
}