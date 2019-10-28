<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Soutenance\Service\IndividuSimulable\IndividuSimulableServiceAwareTrait;
use Zend\Form\Element\Button;
use Zend\Form\Element\Email;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Factory;

class ActeurSimuleForm extends Form {
    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use IndividuSimulableServiceAwareTrait;

    public function init()
    {
        //individu
        $this->add([
            'name' => 'individu',
            'type' => Select::class,
            'options' => [
                'label' => 'Individu* : ',
                'empty_option' => "Sélectionner un individu ... ",
                'value_options' => $this->getIndividuSimulableService()->getIndividusSimulablesAsOptions(),
            ],
            'attributes' => [
                'id'                => 'individu',
                'class'             => 'bootstrap-selectpicker',
                'data-live-search'  => 'true',
            ]
        ]);
        //role
        $this->add([
            'name' => 'role',
            'type' => Select::class,
            'options' => [
                'label' => 'Role* : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Sélectionner un role ... ",
                'value_options' => $this->getRoleService()->getRolesAsGroupOptions(),
            ],
            'attributes' => [
                'id'                => 'role',
                'class'             => 'bootstrap-selectpicker',
                'data-live-search'  => 'true',
            ]
        ]);
        //qualite
        $this->add([
            'type' => Text::class,
            'name' => 'qualite',
            'options' => [
                'label' => "Qualité :",
            ],
            'attributes' => [
                'id' => 'qualite',
            ],
        ]);
        //etablissement
        $this->add([
            'name' => 'etablissement',
            'type' => Select::class,
            'options' => [
                'label' => 'Établissement : ',
                'label_attributes' => [
                    'class' => 'control-label',
                ],
                'empty_option' => "Sélectionner un établissement ... ",
                'value_options' => $this->getEtablissementService()->getEtablissementAsOptions(),
            ],
            'attributes' => [
                'id'                => 'etablissement',
                'class'             => 'bootstrap-selectpicker',
                'data-live-search'  => 'true',
            ]
        ]);

        //button
        $this->add([
            'type' => Button::class,
            'name' => 'creer',
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

        $this->setInputFilter((new Factory())->createInputFilter([
            'individu'      => [ "required" => true ],
            'role'          => [ "required" => true ],
            'qualite'       => [ "required" => false ],
            'etablissement' => [ "required" => false ],
        ]));
    }
}