<?php

namespace Application\Form;

use Application\Form\Validator\NewEmailValidator;
use Application\Form\Validator\PasswordValidator;
use Application\Utils\FormUtils;
use Individu\Entity\Db\Individu;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Password;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Identical;

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

        FormUtils::addSaveButton($this);
    }

    /**
     * @param Individu $individu
     */
    public function setIndividu(Individu $individu)
    {
        $this->individu = $individu;

        $this->get('individuId')->setValue($this->individu->getId());

        if ($this->individu->getEmailPro()) {
            $this->get('email')
                ->setValue($this->individu->getEmailPro())
                ->setAttribute('disabled', true);
        } else {
            $this->get('email')
                ->setAttribute('disabled', false);
        }
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
                'required' => ! $this->individu->getEmailPro(),
                'validators' => [
                    [
                        'name' => NewEmailValidator::class,
                        'options' => ['perimetre' => ['utilisateur']],
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