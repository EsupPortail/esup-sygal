<?php

namespace Doctorant\Form;

use Application\Entity\Db\Rapport;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\EmailAddress;

class ConsentementForm extends Form implements InputFilterProviderInterface
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->setAttribute('method', 'post');

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add([
            'name' => 'consentementListesDiff',
            'type' => Radio::class,
            'options' => [
                'label' => false,
                'value_options' => [
                    'oui' => "Oui",
                    'non' => "Non",
                ],
            ],
            'attributes' => [
                'id' => 'consentementListesDiff',
            ],
        ]);

        $this->add(new Csrf('security'));

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Enregistrer',
            ],
        ]);

        $this->bind(new Consentement());
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'email' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => EmailAddress::class,
                        'options' => [
                            'messages' => [
                                EmailAddress::INVALID_FORMAT => "Le format de l'adresse n'est pas valide",
                            ],
                        ],
                    ],
                ],
            ],

            'consentementListesDiff' => [
                'required' => true,
            ],
        ];
    }
}