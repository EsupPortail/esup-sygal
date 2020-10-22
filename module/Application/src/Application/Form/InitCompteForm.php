<?php

namespace Application\Form;

use Zend\Form\Element\Button;
use Zend\Form\Element\Password;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\Validator\Callback;
use Zend\Validator\Identical;
use Zend\Validator\Regex;

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
                'label' => "Nom d'utilisateur / Username * :",
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
                'label' => "Mot de passe / Password * :",
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
                'label' => "Vérification du mot de passe / Password confirmation * :",
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

        $this->setInputFilter((new Factory())->createInputFilter([
            'username' => [
                'required' => true,
                'validators' => [[
                    'name' => Callback::class,
                    'options' => [
                        'messages' => [
                            Callback::INVALID_VALUE => "Ce nom d'utilisateur ne correspond pas à celui attendu / This username doesn't match with the given one.",
                        ],
                        'callback' => function ($value) {
                            return ($value === $this->username);
                        },
                    ],
                ]],

            ],
            'password1' => [
                'required' => true,
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
                'validators' => [
                    new Identical('password1'),
                ],
            ],
        ]));
    }
}
