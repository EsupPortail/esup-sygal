<?php

namespace These\Form\Generalites;

use Application\Service\Discipline\DisciplineServiceAwareTrait;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use These\Fieldset\Generalites\GeneralitesFieldset;

class GeneralitesForm extends Form
{
    use DisciplineServiceAwareTrait;

    public function init()
    {
        $fieldset = $this->getFormFactory()->getFormElementManager()->get(GeneralitesFieldset::class);
        $fieldset->setUseAsBaseFieldset(true);

        $this
            ->add($fieldset)
            ->add(new Csrf('security'))
            ->add((new Submit('submit'))->setValue('Enregistrer les généralités'));
    }
}