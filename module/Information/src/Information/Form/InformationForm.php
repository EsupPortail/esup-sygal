<?php

namespace Information\Form;

use Application\Utils\FormUtils;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class InformationForm extends Form implements InputFilterProviderInterface {

    public function init()
    {
        // titre
        $this->add([
            'type' => Text::class,
            'name' => 'titre',
            'options' => [
                'label' => "Titre <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
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
                'label' => "Langue de la page <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'value_options' => [
                    'FR' => 'Français',
                    'EN' => 'English',
                ],
                'label_options' => [
                    'disable_html_escape' => true,
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
                    'class' => 'col-form-label',
                ],
            ],
            'attributes' => [
                'class' => 'contenu-page form-control',
//                'class' => 'form-control',
            ]
        ]);

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