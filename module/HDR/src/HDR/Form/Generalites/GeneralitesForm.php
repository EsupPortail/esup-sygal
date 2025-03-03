<?php

namespace HDR\Form\Generalites;

use Application\Utils\FormUtils;
use HDR\Fieldset\Generalites\GeneralitesFieldset;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Form;
use Structure\Entity\Db\Etablissement;

class GeneralitesForm extends Form
{
    private ?Etablissement $etablissement = null;

    public function setEtablissement(?Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    public function init(): void
    {
        /** @var GeneralitesFieldset $fieldset */
        $fieldset = $this->getFormFactory()->getFormElementManager()->get(GeneralitesFieldset::class);
        $fieldset->setName("generalites");
        $fieldset->setUseAsBaseFieldset(true);
        $fieldset->setEtablissement($this->etablissement);

        $this
            ->add($fieldset)
            ->add(new Csrf('security'));
        FormUtils::addSaveButton($this);
    }
}