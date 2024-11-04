<?php

namespace These\Form\Generalites;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use These\Fieldset\Generalites\GeneralitesFieldset;

class GeneralitesForm extends Form
{
    public function init()
    {
        $fieldset = $this->getFormFactory()->getFormElementManager()->get(GeneralitesFieldset::class);
        $fieldset->setName("generalites");
        $fieldset->setUseAsBaseFieldset(true);

        $this
            ->add($fieldset)
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