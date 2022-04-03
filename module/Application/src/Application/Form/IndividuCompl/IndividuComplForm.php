<?php

namespace Application\Form\IndividuCompl;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Laminas\Validator\EmailAddress;
use UnicaenApp\Form\Element\SearchAndSelect;

class IndividuComplForm extends Form {

    /** @var string */
    private $url;

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function init()
    {
        //sas individu
        $individu = new SearchAndSelect('individu', ['label' => "Individu * :"]);
        $individu
            ->setAutocompleteSource($this->url)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'individu',
                'placeholder' => "Agent à ajouter comme individu ...",
            ]);
        $this->add($individu);

        //text email
        $mailValidator = new EmailAddress();
        $mailValidator->setMessages([
            EmailAddress::INVALID_FORMAT =>  'Adresse électronique non valide !',
        ]);
        $this->add(
            (new Email('email'))
                ->setLabel("Adresse électronique :")
                ->setValidator($mailValidator)
        );
        //bouton
        $this->add([
            'type' => Button::class,
            'name' => 'enregistrer',
            'options' => [
                'label' => '<i class="fas fa-save"></i> Enregistrer',
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'btn btn-success',
            ],
        ]);

        //input
        $this->setInputFilter((new Factory())->createInputFilter([
            'individu' => [
                'name' => 'individu',
                'required' => true,
            ],
            'email' => [
                'name' => 'email',
                'required' => true,
                'validator' => [
                    'name' => EmailAddress::class,
                    'messages' => [
                        EmailAddress::INVALID_FORMAT => '',
                    ],
                ],
            ],
        ]));
    }
}