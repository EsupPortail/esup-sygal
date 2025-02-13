<?php

namespace Soutenance\Form\AdresseSoutenance;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory;

class AdresseSoutenanceForm extends Form
{
    public function init(): void
    {
        $this->add([
            'name' => 'ligne1',
            'type' => Text::class,
            'options' => [
                'label' => "Bâtiment et salle <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'form-label',
                ],
            ],
            'attributes' => [
                'class' => 'ligne1',
            ]
        ]);
        $this->add([
            'name' => 'ligne2',
            'type' => Text::class,
            'options' => [
                'label' => "Numéro et voie <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'form-label',
                ],
            ],
            'attributes' => [
                'class' => 'ligne2',
            ]
        ]);
        $this->add([
            'name' => 'ligne3',
            'type' => Text::class,
            'options' => [
                'label' => "Compléments : ",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'form-label',
                ],
            ],
            'attributes' => [
                'class' => 'ligne3',
            ]
        ]);
        $this->add([
            'name' => 'ligne4',
            'type' => Text::class,
            'options' => [
                'label' => "Code postal et ville  <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :",
                'label_options' => [ 'disable_html_escape' => true, ],
                'label_attributes' => [
                    'class' => 'form-label',
                ],
            ],
            'attributes' => [
                'class' => 'ligne4',
            ]
        ]);

        FormUtils::addSaveButton($this);

        $this->setInputFilter((new Factory())->createInputFilter([
            'ligne1'   => [ 'required' => true, ],
            'ligne2'   => [ 'required' => true, ],
            'ligne3'   => [ 'required' => false, ],
            'ligne4'   => [ 'required' => true, ],
        ]));
    }
}