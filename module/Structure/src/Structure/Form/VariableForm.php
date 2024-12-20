<?php

namespace Structure\Form;

use Application\Entity\Db\Variable;
use Application\Service\Variable\VariableServiceAwareTrait;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Callback;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

/**
 * @@property Variable $object
 */
class VariableForm extends Form implements InputFilterProviderInterface
{
    use VariableServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    public function init(): void
    {
        $this->add([
            'type' => Select::class,
            'name' => 'code',
            'options' => [
                'label' => "Code * :",
                'value_options' => array_combine(Variable::CODES, Variable::CODES),
            ],
            'attributes' => [
                'id' => 'code',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'description',
            'options' => [
                'label' => "Description * :",
            ],
            'attributes' => [
                'id' => 'description',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'valeur',
            'options' => [
                'label' => "Valeur * :",
            ],
            'attributes' => [
                'id' => 'valeur',
            ],
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'dateDebutValidite',
            'options' => [
                'label' => "Début de validité :",
            ],
            'attributes' => [
                'readonly' => 'true',
            ]
        ]);

        $this->add([
            'type' => Date::class,
            'name' => 'dateFinValidite',
            'options' => [
                'label' => "Fin de validité :",
            ],
            'attributes' => [
                'readonly' => 'true',
            ]
        ]);

        $this
            ->add(new Csrf('security'))
            ->add((new Submit('submit'))->setValue('Enregistrer'));
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'code' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => fn (string $code) =>
                                $this->variableService->getRepository()->findOneBy([
                                    'code' => $code,
                                    'etablissement' => $this->object->getEtablissement(),
                                ]) === null,
                            'messages' => [
                                Callback::INVALID_VALUE => "Pour cet établissement, une variable existe déjà avec ce code"
                            ],
                        ],
                    ],
                ]
            ],
            'description' => [
                'required' => true,
            ],
            'valeur' => [
                'required' => true,
            ],
            'dateDebutValidite' => [
                'required' => false,
            ],
            'dateFinValidite' => [
                'required' => false,
            ],
        ];
    }
}