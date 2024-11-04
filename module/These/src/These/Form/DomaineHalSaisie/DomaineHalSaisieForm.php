<?php

namespace These\Form\DomaineHalSaisie;

use Application\Utils\FormUtils;
use Laminas\Form\Form;
use These\Form\DomaineHalSaisie\Fieldset\DomaineHalFieldset;

class DomaineHalSaisieForm extends Form
{
    public function init(): void
    {
         /** @var DomaineHalFieldset $domaineHalFieldset */
        $domaineHalFieldset = $this->getFormFactory()->getFormElementManager()->get(DomaineHalFieldset::class);
        $domaineHalFieldset->setUseAsBaseFieldset(true);
        $this->add($domaineHalFieldset, ['name' => 'domaineHalFieldset']);

        FormUtils::addSaveButton($this);
    }
}