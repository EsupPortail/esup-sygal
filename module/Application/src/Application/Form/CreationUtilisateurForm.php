<?php

namespace Application\Form;

use Application\Form\Validator\NewEmailValidator;
use Application\Form\Validator\PasswordValidator;
use Zend\Form\Element\Password;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Mvc\Application;

class CreationUtilisateurForm extends Form implements InputFilterProviderInterface
{



    public function init()
    {
        //$this->setObject(new RdvBu());

        $this->add(
            (new Hidden('id'))
        );
        $this->add(
            (new Text('civilite'))
                ->setLabel("Civilité :")
        );
        $this->add(
            (new Text('nomUsuel'))
                ->setLabel("Nom usuel :")
        );

        $this->add(
            (new Text('nomPatronymique'))
            ->setLabel("Nom Patronymique :")
        );
        $this->add(
            (new Text('prenom'))
                ->setLabel("Prénom :")
        );
        $this->add(
            (new Text('email'))
                ->setLabel("Email :")
        );
        $this->add(
            (new Password('password'))
                ->setLabel("Mot de passe :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

        //$this->setInputFilter((new Factory())->createInputFilter());
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
            'civilite' => [
                'name' => 'civilite',
                'required' => true,
            ],
            'nomUsuel' => [
                'name' => 'nomUsuel',
                'required' => true,
            ],
            'nomPatronymique' => [
                'name' => 'nomPatronymique',
                'required' => false,
            ],
            'prenom' => [
                'name' => 'prenom',
                'required' => true,
            ],
            'email' => [
                'name' => 'email',
                'required' => true,
                'validators' => [
                    [
                        'name' => NewEmailValidator::class,
                    ],
                ],
            ],
            'password' => [
                'name' =>'password',
                'required' => true,
                'validators' => [
                    [
                        'name' => PasswordValidator::class,
                    ],
                ],
            ],
        ];
    }
}