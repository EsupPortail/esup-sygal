<?php

namespace Admission\Form\Transmission;

use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class TransmissionForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setAttribute('method', 'post');

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Text('codeVoeu'))
                ->setLabel("Code voeu")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Text('codePeriode'))
                ->setLabel("Code période")
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(new Csrf('security'), ['csrf_options' => ['timeout' => 60]]);

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Enregistrer',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'codeVoeu' => [
                'name' => 'codeVoeu',
                'required' => true,
            ],
            'codePeriode' => [
                'name' => 'codePeriode',
                'required' => true,
            ]
        ];
    }
}