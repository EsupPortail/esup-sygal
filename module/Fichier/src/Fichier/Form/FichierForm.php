<?php

namespace Fichier\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class FichierForm extends Form
{
    public function init(): void
    {
        parent::init();

        $this->add([
            'type' => Text::class,
            'name' => 'idPermanent',
            'options' => [
                'label' => "Id permanent",
            ],
            'attributes' => [
                'id' => 'idPermanent',
            ],
        ]);

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'idPermanent' => [ 'required' => false, ],
        ]));
    }
}