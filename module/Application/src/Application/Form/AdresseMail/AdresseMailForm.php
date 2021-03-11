<?php

namespace Application\Form\AdresseMail;

use Application\Form\Validator\NewEmailValidator;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class AdresseMailForm extends Form {

    public function init() {

        $this->add(
            (new Text('email'))
                ->setLabel("Adresse électronique (identifiant de connexion) :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'email' => [
                'name' => 'email',
                'required' => true,
                'validators' => [
                    [
                        'name' => NewEmailValidator::class,
                    ],
                ],
            ],
        ];
    }
}