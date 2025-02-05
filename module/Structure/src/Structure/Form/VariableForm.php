<?php

namespace Structure\Form;

use Application\Entity\Db\Variable;
use Application\Service\Variable\VariableServiceAwareTrait;
use Application\Utils\FormUtils;
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

    public function prepare()
    {
        /** @var Variable $variable */
        $variable = $this->getObject();
        $codeModifiable = !$variable->getId();

        $this->get('code')->setAttribute('disabled', !$codeModifiable);

        return parent::prepare();
    }

    public function init(): void
    {
        $this->add([
            'type' => Select::class,
            'name' => 'code',
            'options' => [
                'label' => "Code <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'value_options' => array_combine(Variable::CODES, Variable::CODES),
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'code',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'description',
            'options' => [
                'label' => "Description <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
            ],
            'attributes' => [
                'id' => 'description',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'valeur',
            'options' => [
                'label' => "Valeur <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [
                    'disable_html_escape' => true,
                ],
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

        $this->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }

    public function getInputFilterSpecification(): array
    {
        /** @var Variable $variable */
        $variable = $this->getObject();
        $codeModifiable = !$variable->getId();
        return [
            'code' => [
                'required' => $codeModifiable,
                'validators' => [
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function (string $code) {
                                $existingVariable = $this->variableService->getRepository()->findOneBy([
                                    'code' => $code,
                                    'etablissement' => $this->object->getEtablissement(),
                                ]);
                                // Si aucune variable avec ce code n'existe, autoriser la création
                                if ($existingVariable === null) {
                                    return true;
                                }
                                // Si une variable existe, vérifier si c'est une mise à jour (l'objet actuel a un ID)
                                return $this->object->getId() !== null;
                            },
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