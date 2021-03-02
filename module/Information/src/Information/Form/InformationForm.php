<?php

namespace Information\Form;

use Zend\Filter\StripTags;
use Zend\Form\Element\Button;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class InformationForm extends Form implements InputFilterProviderInterface {

    public function init()
    {
        // titre
        $this->add([
            'type' => Text::class,
            'name' => 'titre',
            'options' => [
                'label' => "Titre :",
            ],
            'attributes' => [
                'id' => 'titre',
            ],
        ]);
        $this->add([
            'type' => Radio::class,
            'name' => 'visible',
            'options' => [
                'label' => 'Visibilité de la page :',
                'value_options' => [
                    '1' => 'Visible',
                    '0' => 'Cachée',
                ],
                'attributes' => [
                    'class' => 'radio-inline',
                ],
            ],
        ]);
        $this->add([
            'type' => Select::class,
            'name' => 'langue',
            'options' => [
                'label' => 'Langue de la page :',
                'value_options' => [
                    'FR' => 'Français',
                    'EN' => 'English',
                ],
            ],
        ]);
        // priorite
        $this->add([
            'type' => Text::class,
            'name' => 'priorite',
            'options' => [
                'label' => "Priorite (valeur numérique) :",
            ],
            'attributes' => [
                'id' => 'priorite',
            ],
        ]);
        // contenu
        $this->add([
            'name' => 'contenu',
            'type' => 'textarea',
            'options' => [
                'label' => 'Contenu : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
            ],
            'attributes' => [
                'class' => 'contenu-page form-control',
//                'class' => 'form-control',
            ]
        ]);

        // button
        $this->add([
            'type' => Button::class,
            'name' => 'creer',
            'options' => [
                'label' => 'Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);
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
            'langue' => ['required' => true],
            'titre' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            'contenu' => [
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => StripTags::class,
                        'options' => [
                            'allowTags' => [
                                'p', 'strong', 'em', 'ul', 'ol', 'li', 'a', 'h2', 'h3', 'h4', 'table', 'theader', 'th', 'tbody', 'tr', 'td'
                            ],
                            'allowAttribs' => [
                                'href', 'title', 'target'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}