<?php
namespace Admission\Form\Fieldset\Verification;

use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

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
                            "class" => "bouton_gestionnaire incomplet",
                        ],
                        'label_attributes' => [
                            'class' => 'btn btn-danger',
                        ],
                    ],
                    [
                        'value' => '1',
                        'label' => 'Complet',
                        'attributes' => [
                            "class" => "bouton_gestionnaire complet",
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
                ->setLabel("Observations à donner à l'étudiant")
                ->setAttributes([
                    "class" => "commentaires_gestionnaire"
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
            ],
        ];
    }
}