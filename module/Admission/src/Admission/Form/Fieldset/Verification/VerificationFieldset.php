<?php
namespace Admission\Form\Fieldset\Verification;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;

class VerificationFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->add(
            (new Hidden('id'))
        );

        $this->add([
            'type' => Radio::class,
            'name' => 'estComplet',
            'options' => [
                'value_options' => [
                    [
                        'value' => '0',
                        'label' => 'Incomplet',
                        'attributes' => [
                            "class" => "bouton-gestionnaire incomplet",
                        ],
                        'label_attributes' => [
                            'class' => 'btn btn-danger',
                        ],
                    ],
                    [
                        'value' => '1',
                        'label' => 'Complet',
                        'attributes' => [
                            "class" => "bouton-gestionnaire complet",
                        ],
                        'label_attributes' => [
                            'class' => 'btn btn-success',
                        ],
                    ],
                ],
            ],
        ]);

        $this->add(
            (new Textarea('commentaire'))
                ->setLabel("Observations à donner à l'étudiant (obligatoire pour passer à une autre étape)")
                ->setAttributes([
                    "class" => "description_commentaires_gestionnaire"
                ])
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'commentaire' => [
                'name' => 'commentaire',
                'required' => false,

            ],
            'estComplet' => [
                'name' => 'estComplet',
                'required' => false,
                //Si le gestionnaire signale le fieldset comme incomplet, il doit obligatoirement fournir un commentaire
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'messages' => [
                                Callback::INVALID_VALUE => "Le commentaire est requis lorsque le formulaire est signalé comme incomplet.",
                            ],
                            'callback' => function ($value, $context = []) {
                                if ((isset($context['estComplet']) && $context['estComplet'] == 0) && empty($context['commentaire'])) {
                                    return false;
                                }

                                return true;
                            },
                            'break_chain_on_failure' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}