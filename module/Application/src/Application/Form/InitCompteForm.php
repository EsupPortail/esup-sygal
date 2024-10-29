<?php

namespace Application\Form;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Password;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Laminas\Validator\Callback;
use Laminas\Validator\Identical;
use Laminas\Validator\Regex;

class InitCompteForm extends Form
{
    /** @var string */
    private $username;

    /**
     * @param string $username
     * @return InitCompteForm
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function init()
    {
        // libelle
        $this->add([
            'type' => Text::class,
            'name' => 'username',
            'options' => [
                'label' => "Identifiant de connexion / Username <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'username',
            ],
        ]);
        //password1
        $this->add([
            'type' => Password::class,
            'name' => 'password1',
            'options' => [
                'label' => "Mot de passe / Password <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'password1',
            ],
        ]);
        //password2
        $this->add([
            'type' => Password::class,
            'name' => 'password2',
            'options' => [
                'label' => "Confirmation du mot de passe / Password confirmation <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'password2',
            ],
        ]);
        //submit
        $this->add([
            'type' => Button::class,
            'name' => 'bouton',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer / Save',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        $this->add(new Csrf('csrf'));

        $this->setInputFilter((new Factory())->createInputFilter([
            'username' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                ],
                'validators' => [[
                    'name' => Callback::class,
                    'options' => [
                        'messages' => [
                            Callback::INVALID_VALUE => "Cet identifiant de connexion ne correspond pas à celui attendu / This username doesn't match with the given one.",
                        ],
                        'callback' => function ($value) {
                            return ($value === $this->username);
                        },
                    ],
                ]],

            ],
            'password1' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*_])(?=.{8,})/',
                            'messages' => [
                                Regex::NOT_MATCH => "Le mot de passe choisi ne respecte pas les consignes de sécurité / This password is not consistent with the security constraints.",
                            ],
                        ],
                    ],],
            ],
            'password2' => [
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    new Identical('password1'),
                ],
            ],
        ]));
    }
}
