<?php

namespace These\Form\Encadrement;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;
use These\Fieldset\Encadrement\EncadrementFieldset;

class EncadrementForm extends Form
{
    use EtablissementServiceAwareTrait;
    use QualiteServiceAwareTrait;

    public function init()
    {
        $this->setObject(new These());

        $fieldset = $this->getFormFactory()->getFormElementManager()->get(EncadrementFieldset::class);
        $fieldset->setUseAsBaseFieldset(true);

        $this
            ->add($fieldset)
            ->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }
}