<?php

namespace Individu\Form\IndividuRole;

use Individu\Fieldset\IndividuRoleEtablissement\IndividuRoleEtablissementFieldset;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenApp\Form\Element\Collection;

/**
 * @property \Individu\Entity\Db\IndividuRole $object
 */
class IndividuRoleForm extends Form implements InputFilterProviderInterface
{
    private IndividuRoleEtablissementFieldset $individuRoleEtablissementFieldsetPrototype;

    public function init(): void
    {
        $this->individuRoleEtablissementFieldsetPrototype = $this->getFormFactory()->getFormElementManager()->get(
            IndividuRoleEtablissementFieldset::class
        );

        $individu = new Text('individu', ['label' => "Individu <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :"]);
        $individu->setLabelOptions(['disable_html_escape' => true,]);
        $this->add($individu);

        $role = new Text('role', ['label' => "Rôle <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :"]);
        $role->setLabelOptions(['disable_html_escape' => true,]);
        $this->add($role);

        $individuRoleEtablissementCollection = new Collection('individuRoleEtablissement');
        $individuRoleEtablissementCollection
            ->setLabel("Périmètre :")
            ->setMinElements(0)
            ->setOptions([
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->individuRoleEtablissementFieldsetPrototype,
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($individuRoleEtablissementCollection);

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
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    public function bind(object $object, int $flags = FormInterface::VALUES_NORMALIZED): static
    {
        /** @var \Individu\Entity\Db\IndividuRole $object */

        /** @var \Individu\Entity\Db\IndividuRoleEtablissement $individuRoleEtablissement */
        $individuRoleEtablissement = $this->individuRoleEtablissementFieldsetPrototype->getObject();
        $individuRoleEtablissement->setIndividuRole($object);

        return parent::bind($object, $flags);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'etablissement' => [
                'name' => 'etablissement',
                'required' => false,
            ],
        ];
    }
}