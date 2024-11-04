<?php

namespace These\Form\Financement;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use These\Fieldset\Financement\FinancementFieldset;
use UnicaenApp\Form\Element\Collection;

class FinancementsForm extends Form
{
    public function init()
    {
        $financements = new Collection('financements');
        $financements
            ->setMinElements(0)
            ->setOptions([
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $this->getFormFactory()->getFormElementManager()->get(
                    FinancementFieldset::class
                ),
            ])
            ->setAttributes([
                'class' => 'collection',
            ]);
        $this->add($financements);

        $this
            ->setAttribute('formName', 'financementsForm')
            ->add(new Csrf('security'))
            ->add([
                'type' => Button::class,
                'name' => 'submit',
                'options' => [
                    'label' => '<span class="icon icon-save"></span> Enregistrer',
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
}