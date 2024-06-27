<?php

namespace These\Form;

use Laminas\Form\ElementInterface;
use Laminas\Form\FormElementManager;
use These\Fieldset\Confidentialite\ConfidentialiteFieldset;
use These\Fieldset\Confidentialite\ConfidentialiteFieldsetFactory;
use These\Fieldset\Direction\DirectionFieldset;
use These\Fieldset\Direction\DirectionFieldsetFactory;
use These\Fieldset\Encadrement\EncadrementFieldset;
use These\Fieldset\Encadrement\EncadrementFieldsetFactory;
use These\Fieldset\Generalites\GeneralitesFieldset;
use These\Fieldset\Generalites\GeneralitesFieldsetFactory;
use These\Fieldset\Structures\StructuresFieldset;
use These\Fieldset\Structures\StructuresFieldsetFactory;
use These\Form\Confidentialite\ConfidentialiteForm;
use These\Form\Confidentialite\ConfidentialiteFormFactory;
use These\Form\Direction\DirectionForm;
use These\Form\Direction\DirectionFormFactory;
use These\Form\Encadrement\EncadrementForm;
use These\Form\Encadrement\EncadrementFormFactory;
use These\Form\Generalites\GeneralitesForm;
use These\Form\Generalites\GeneralitesFormFactory;
use These\Form\Structures\StructuresForm;
use These\Form\Structures\StructuresFormFactory;

class TheseFormsManager extends FormElementManager
{
    protected $factories = [

        // Forms
        ConfidentialiteForm::class => ConfidentialiteFormFactory::class,
        EncadrementForm::class => EncadrementFormFactory::class,
        GeneralitesForm::class => GeneralitesFormFactory::class,
        DirectionForm::class => DirectionFormFactory::class,
        StructuresForm::class => StructuresFormFactory::class,
        // Fieldsets
        ConfidentialiteFieldset::class => ConfidentialiteFieldsetFactory::class,
        EncadrementFieldset::class => EncadrementFieldsetFactory::class,
        GeneralitesFieldset::class => GeneralitesFieldsetFactory::class,
        DirectionFieldset::class => DirectionFieldsetFactory::class,
        StructuresFieldset::class => StructuresFieldsetFactory::class,
    ];

    protected $instanceOf = ElementInterface::class;
}