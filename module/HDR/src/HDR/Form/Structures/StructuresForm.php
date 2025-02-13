<?php

namespace HDR\Form\Structures;

use Application\Utils\FormUtils;
use HDR\Entity\Db\HDR;
use HDR\Fieldset\Structures\StructuresFieldset;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class StructuresForm extends Form
{
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    public function init()
    {
        $this->setObject(new HDR());

        $fieldset = $this->getFormFactory()->getFormElementManager()->get(StructuresFieldset::class);
        $fieldset->setName("structures");
        $fieldset->setUseAsBaseFieldset(true);

        $this
            ->add($fieldset)
            ->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }
}