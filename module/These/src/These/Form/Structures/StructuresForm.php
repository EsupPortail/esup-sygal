<?php

namespace These\Form\Structures;

use Application\Utils\FormUtils;
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
            ->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }
}