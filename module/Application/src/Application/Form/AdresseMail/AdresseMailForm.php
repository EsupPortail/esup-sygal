<?php

namespace Application\Form\AdresseMail;

use Application\Form\Validator\NewEmailValidator;
use Application\Utils\FormUtils;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;

class AdresseMailForm extends Form {

    public function init() {

        $this->add(
            (new Text('email'))
                ->setLabel("Adresse électronique (identifiant de connexion) <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
        );

        FormUtils::addSaveButton($this);
    }

    /**
     * Should return an array specification compatible with
     * {@link Laminas\InputFilter\Factory::createInputFilter()}.
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
                        'options' => ['perimetre' => ['utilisateur']],
                    ],
                ],
            ],
        ];
    }
}