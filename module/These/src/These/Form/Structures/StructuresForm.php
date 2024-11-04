<?php

namespace These\Form\Structures;

use Laminas\Form\Element\Button;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;
use These\Fieldset\Structures\StructuresFieldset;

class StructuresForm extends Form
{
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    public function init()
    {
        $this->setObject(new These());

        $fieldset = $this->getFormFactory()->getFormElementManager()->get(StructuresFieldset::class);
        $fieldset->setName("structures");
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