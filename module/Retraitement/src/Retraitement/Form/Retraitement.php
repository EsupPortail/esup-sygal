<?php

namespace Retraitement\Form;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * Created by PhpStorm.
 * User: brossard
 * Date: 27/09/16
 * Time: 15:21
 */
class Retraitement extends Form implements InputFilterProviderInterface  {

    public function __construct($name, array $options)
    {
        parent::__construct($name, $options);

        $files = $options['files'];
        $commands = $options['commands'];

        $this->add([
                'type'  => 'MultiCheckbox',
                'name'  => 'commands',
                'options' => [
                    'value_options' => $commands,
                    'label' => 'Cocher la ou les moulinettes à appliquer :',
                ]
        ]);

        $this->add([
            'type'  => 'MultiCheckbox',
            'name'  => 'files',
            'options' => [
                'value_options' => $files,
                'label' => 'Cocher les fichiers que vous voulez retraiter :',
            ]
        ]);

        $this->add([
            'name'  => 'submit',
            'type'  => 'Submit',
            'attributes'=> [
                'value' => 'Lancer le traitement',
                'class' => 'btn btn-primary',
            ]
        ]);
    }

    /**
     * Should return an array specification compatible with
     * {@link Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification() {

        return [
            'files' => [
                'required' => true,
            ],
            'commands' => [
                'required' => true,
            ]
        ];

    }
}