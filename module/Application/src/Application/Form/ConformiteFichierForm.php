<?php

namespace Application\Form;

use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class ConformiteFichierForm extends Form
{
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, $options);

        $this->add(
            (new Radio('conforme'))
                ->setValueOptions([
                    '1' => "est <strong>conforme</strong> et qu'elle peut être archivée en l'état." ,
                    '0' => "n'est <strong>pas conforme</strong> et qu'elle ne peut pas être archivée en l'état.",
                ])
                ->setLabel("")
                ->setLabelOptions([
                    'disable_html_escape' => true,
                ])
        );
        $this->add(
            (new Submit('submit_certifConformite'))
                ->setValue("Enregistrer")
                ->setAttribute('class', 'btn btn-primary')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'conforme' => [
                'name' => 'conforme',
                'required' => true,
            ]
        ]));
    }
}