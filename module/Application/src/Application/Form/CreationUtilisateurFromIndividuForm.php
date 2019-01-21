<?php

namespace Application\Form;

use Application\Entity\Db\Individu;
use Application\Form\Validator\NewEmailValidator;
use Application\Form\Validator\PasswordValidator;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Password;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Identical;

class CreationUtilisateurFromIndividuForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var Individu
     */
    private $individu;

    public function init()
    {
        $this->add(
            (new Hidden('id'))
        );

        $this->add(
            (new Hidden('individuId'))
        );

        $this->add(
            (new Text('email'))
                ->setLabel("Adresse électronique (identifiant de connexion) :")
        );

        $this->add(
            (new Password('password'))
                ->setLabel("Mot de passe :")
        );

        $this->add(
            (new Password('passwordbis'))
                ->setLabel("Confirmation du mot de passe :")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );
    }

    /**
     * @param Individu $individu
     */
    public function setIndividu(Individu $individu)
    {
        $this->individu = $individu;

        $this->get('individuId')->setValue($this->individu->getId());

        if ($this->individu->getEmail()) {
            $this->get('email')
                ->setValue($this->individu->getEmail())
                ->setAttribute('disabled', true);
        } else {
            $this->get('email')
                ->setAttribute('disabled', false);
        }
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
                'required' => ! $this->individu->getEmail(),
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
            'passwordbis' => [
                'name' =>'passwordbis',
                'required' => true,
                'validators' => [
                    new Identical('password'),
                ],
            ],
        ];
    }
}