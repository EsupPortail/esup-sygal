<?php

namespace Individu\Form\IndividuCompl;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Laminas\Validator\EmailAddress;
use UnicaenApp\Form\Element\SearchAndSelect;

class IndividuComplForm extends Form implements InputFilterProviderInterface
{
    private string $urlIndividu;
    private bool $emailRequired = true;

    public function setUrlIndividu(string $urlIndividu): void
    {
        $this->urlIndividu = $urlIndividu;
    }

    public function init(): void
    {
        //sas individu
        $individu = new SearchAndSelect('individu', ['label' => "Individu <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :"]);
        $individu
            ->setLabelOptions([ 'disable_html_escape' => true, ])
            ->setAutocompleteSource($this->urlIndividu)
            ->setSelectionRequired()
            ->setAttributes([
                'id' => 'individu',
                'placeholder' => "Agent à ajouter comme individu ...",
            ]);
        $this->add($individu);

        // adresse email initiale
        $this->add(
            (new Text('individuEmail'))
                ->setLabel("Adresse électronique professionnelle initiale :")
        );

        // adresse email de remplacement
        $mailValidator = new EmailAddress();
        $mailValidator->setMessages([
            EmailAddress::INVALID_FORMAT => 'Adresse électronique non valide !',
        ]);
        $this->add(
            (new Email('email'))
                ->setLabel("Adresse électronique professionnelle de remplacement <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions([ 'disable_html_escape' => true, ])
                ->setAttribute('placeholder' , "Adresse électronique professionnelle")
                ->setAttribute('required', $this->emailRequired)
                ->setValidator($mailValidator)
        );

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
                'value' => 'enregistrer',
                'type' => 'submit',
                'class' => 'btn btn-success',
            ],
        ]);
        $this->add([
            'type' => Button::class,
            'name' => 'detruire',
            'options' => [
                'label' => "<i class='fas fa-trash'></i> Supprimer ce complément d'individu",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'value' => 'detruire',
                'type' => 'submit',
                'class' => 'btn btn-danger',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'individu' => [
                'name' => 'individu',
                'required' => true,
            ],
            'email' => [
                'name' => 'email',
                'required' => $this->emailRequired,
                'validators' => [
                    [
                        'name' => EmailAddress::class,
                        'messages' => [
                            EmailAddress::INVALID_FORMAT => 'Adresse électronique non valide.',
                        ],
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => "L'adresse de remplacement doit être différente de l'adresse initiale.",
                            ],
                            'callback' => fn($value) => $value <> $this->get('individuEmail')->getValue(),
                        ],
                    ],
                ],
            ],
        ];
    }
}