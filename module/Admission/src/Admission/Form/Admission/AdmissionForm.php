<?php

namespace Admission\Form\Admission;

use Admission\Entity\Db\Admission;
use Admission\Form\Fieldset\Individu\IndividuFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Form\Fieldset\Validation\ValidationFieldset;
use Laminas\Form\Element\Collection;
use UnicaenApp\Form\Fieldset\MultipageFormNavFieldset;
use UnicaenApp\Form\MultipageForm;

/**
 * @property Admission $object
 */
class AdmissionForm extends MultipageForm
{
    public function init()
    {
        parent::init();

        $individuFieldset = $this->getFormFactory()->getFormElementManager()->get(IndividuFieldset::class);
        $individuFieldset->setName("individu");
        $this->add($individuFieldset);

        $inscriptionFieldset = $this->getFormFactory()->getFormElementManager()->get(InscriptionFieldset::class);
        $inscriptionFieldset->setName("inscription");
        $this->add($inscriptionFieldset);

        $financementFieldset = $this->getFormFactory()->getFormElementManager()->get(FinancementFieldset::class);
        $financementFieldset->setName("financement");
        $this->add($financementFieldset);

        $validationFieldset = $this->getFormFactory()->getFormElementManager()->get(ValidationFieldset::class);
        $validationFieldset->setName("validation");
        $this->add($validationFieldset);

//        $this->addConfirmFieldset();

        $this->setNavigationFieldsetPrototype($this->createNavigationFieldsetPrototype());
    }
    /**
     * @return MultipageFormNavFieldset
     */
    protected function createNavigationFieldsetPrototype()
    {
        $navigationElement = MultipageFormNavFieldset::create();

        $nextButton = $navigationElement->getNextButton();
        $prevButton = $navigationElement->getPreviousButton();
        $cancelButton = $navigationElement->getCancelButton();
        $submitButton = $navigationElement->getSubmitButton();
        $confirmButton = $navigationElement->getConfirmButton();

        // ajouts de classes CSS
        $nextButton->setAttribute('class', $nextButton->getAttribute('class') . ' btn btn-primary');
        $nextButton->setValue('Suivant et enregistrer >');

        $prevButton->setAttribute('class', $prevButton->getAttribute('class') . ' btn btn-primary');
        $cancelButton->setAttribute('class', $cancelButton->getAttribute('class') . ' btn btn-danger');
        $submitButton->setAttribute('class', $submitButton->getAttribute('class') . ' btn btn-success');
        $submitButton->setValue('Envoyer aux gestionnaires');
        $confirmButton->setAttribute('class', $confirmButton->getAttribute('class') . ' btn btn-success');

        return $navigationElement;
    }
}