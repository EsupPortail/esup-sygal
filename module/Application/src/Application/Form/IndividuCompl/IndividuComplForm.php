<?php

namespace Application\Form\IndividuCompl;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Email;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;
use Laminas\Validator\EmailAddress;
use UnicaenApp\Form\Element\SearchAndSelect;

class IndividuComplForm extends Form {

    /** @var string */
    private $urlIndividu;

    public function setUrlIndividu(string $urlIndividu)
    {
        $this->urlIndividu = $urlIndividu;
    }

    /** @var string */
    private $urlEtablissement;

    public function setUrlEtablissement(string $urlEtablissement)
    {
        $this->urlEtablissement = $urlEtablissement;
    }

    /** @var string */
    private $urlUniteRecherche;

    public function setUrlUniteRecherche(string $urlUniteRecherche)
    {
        $this->urlUniteRecherche = $urlUniteRecherche;
    }

    public function init()
    {
        //sas individu
        $individu = new SearchAndSelect('individu', ['label' => "Individu * :"]);
        $individu
            ->setAutocompleteSource($this->urlIndividu)
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
                ->setLabel("Adresse électronique professionnelle * :")
                ->setAttribute('placeholder' , "Adresse électronique professionnelle associée à l'individu ...")
                ->setValidator($mailValidator)
        );
        //sas etablissement
        $individu = new SearchAndSelect('etablissement', ['label' => "Établissement :"]);
        $individu
            ->setAutocompleteSource($this->urlEtablissement)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'etablissement',
                'placeholder' => "Établissement associé à l'individu ...",
            ]);
        $this->add($individu);
        //sas unite
        $individu = new SearchAndSelect('uniteRecherche', ['label' => "Unité de recherche :"]);
        $individu
            ->setAutocompleteSource($this->urlUniteRecherche)
            ->setSelectionRequired(true)
            ->setAttributes([
                'id' => 'uniteRecherche',
                'placeholder' => "Unité de recherche associée à l'individu ...",
            ]);
        $this->add($individu);

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
            'etablissement' => [
                'name' => 'etablissement',
                'required' => false,
            ],
            'uniteRecherche' => [
                'name' => 'uniteRecherche',
                'required' => false,
            ],
        ]));
    }
}