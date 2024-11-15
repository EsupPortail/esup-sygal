<?php

namespace Depot\Form\Description;

use Application\Utils\FormUtils;
use Depot\Form\Metadonnees\MetadonneeTheseFieldset;
use Laminas\Form\Form;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldset;

class DescriptionTheseForm extends Form
{
    public function init()
    {
        $metadonneesFieldset = $this->getFormFactory()->getFormElementManager()->get(MetadonneeTheseFieldset::class);
        $metadonneesFieldset->setName("metadonneeThese");

        $this->add($metadonneesFieldset);

        $domainesHalFieldset = $this->getFormFactory()->getFormElementManager()->get(DomaineHalFieldset::class);
        $domainesHalFieldset->setName("domaineHal");
        $domainesHalFieldset->setDomainesHalRequired(true);

        $this->add($domainesHalFieldset);

        FormUtils::addSaveButton($this);
    }
}