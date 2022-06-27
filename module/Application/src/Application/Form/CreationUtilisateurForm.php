<?php

namespace Application\Form;

use Application\Form\Validator\NewEmailValidator;
use Individu\Entity\Db\Individu;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class CreationUtilisateurForm extends Form implements InputFilterProviderInterface
{
    public function init()
    {
        $this->add(
            (new Hidden('id'))
        );
        $this->add(
            (new Radio('civilite'))
                ->setValueOptions([
                    Individu::CIVILITE_M => Individu::CIVILITE_M,
                    Individu::CIVILITE_MME => Individu::CIVILITE_MME,
                ])
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
                ->setLabel("Adresse électronique (identifiant de connexion) :")
        );
        $this->add(
            (new Checkbox('individu'))
                ->setLabel("Création d'individu pour cet utilisateur <br/> <span class='text-danger'><span class='fas fa-exclamation-triangle'></span> Ne pas cocher si pour lier à un individu existant</span>")
        );
        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    /**
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
            'individu' => [
                'name' => 'individu',
                'required' => false,
            ],
        ];
    }
}