<?php

namespace These\Form\Financement;

use Application\Utils\FormUtils;
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
            ->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }
}