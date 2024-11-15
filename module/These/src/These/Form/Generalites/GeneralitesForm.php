<?php

namespace These\Form\Generalites;

use Application\Utils\FormUtils;
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
            ->add(new Csrf('security'));
        FormUtils::addSaveButton($this);
    }
}