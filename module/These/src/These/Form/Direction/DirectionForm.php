<?php

namespace These\Form\Direction;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Fieldset\Direction\DirectionFieldset;

class DirectionForm extends Form
{
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;

    public function init()
    {
        $this->setObject(new These());

        $fieldset = $this->getFormFactory()->getFormElementManager()->get(DirectionFieldset::class);
        $fieldset->setName("direction");
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