<?php

namespace These\Form\Direction;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
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
            ->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }
}