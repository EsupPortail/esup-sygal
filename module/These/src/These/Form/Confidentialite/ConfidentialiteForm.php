<?php

namespace These\Form\Confidentialite;

use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use These\Entity\Db\These;
use These\Fieldset\Confidentialite\ConfidentialiteFieldset;

class ConfidentialiteForm extends Form
{
    public function init()
    {
        $this->setObject(new These());

        $fieldset = $this->getFormFactory()->getFormElementManager()->get(ConfidentialiteFieldset::class);
        $fieldset->setUseAsBaseFieldset(true);

        $this
            ->add($fieldset)
            ->add(new Csrf('security'))
            ->add((new Submit('submit'))->setValue('Enregistrer'));
    }
}