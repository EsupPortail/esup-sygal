<?php

namespace HDR\Form\HDRSaisie;

use Application\Utils\FormUtils;
use HDR\Fieldset\Direction\DirectionFieldset;
use HDR\Fieldset\Generalites\GeneralitesFieldset;
use HDR\Fieldset\Structures\StructuresFieldset;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;

class HDRSaisieForm extends Form
{
    public function init(): void
    {
        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $generalitesFieldset = $this->getFormFactory()->getFormElementManager()->get(GeneralitesFieldset::class);
        $generalitesFieldset->setName("generalites");
        $generalitesFieldset->setLabel("Informations générales");
        $this->add($generalitesFieldset);

        $structuresFieldset = $this->getFormFactory()->getFormElementManager()->get(StructuresFieldset::class);
        $structuresFieldset->setName("structures");
        $structuresFieldset->setLabel("Structures encadrantes");
        $this->add($structuresFieldset);

        $directionFieldset = $this->getFormFactory()->getFormElementManager()->get(DirectionFieldset::class);
        $directionFieldset->setName("direction");
        $directionFieldset->setLabel("Garant de l'HDR");
        $this->add($directionFieldset);

        $this
            ->add(new Csrf('security'));

        FormUtils::addSaveButton($this);
    }
}