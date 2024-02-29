<?php

namespace Admission\Form\Admission;

use Admission\Entity\Db\Admission;
use Admission\Form\Fieldset\Document\DocumentFieldset;
use Admission\Form\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Form\Fieldset\Financement\FinancementFieldset;
use Admission\Form\Fieldset\Inscription\InscriptionFieldset;
use Admission\Form\Fieldset\Validation\AdmissionValidationFieldset;
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

        $etudiantFieldset = $this->getFormFactory()->getFormElementManager()->get(EtudiantFieldset::class);
        $etudiantFieldset->setName("etudiant");
        $this->add($etudiantFieldset);

        $inscriptionFieldset = $this->getFormFactory()->getFormElementManager()->get(InscriptionFieldset::class);
        $inscriptionFieldset->setName("inscription");
        $this->add($inscriptionFieldset);

        $financementFieldset = $this->getFormFactory()->getFormElementManager()->get(FinancementFieldset::class);
        $financementFieldset->setName("financement");
        $this->add($financementFieldset);

        $documentFieldset = $this->getFormFactory()->getFormElementManager()->get(DocumentFieldset::class);
        $documentFieldset->setName("document");
        $this->add($documentFieldset);

        $this->setNavigationFieldsetPrototype($this->createNavigationFieldsetPrototype());
    }
    /**
     * @return MultipageFormNavFieldset
     */
    protected function createNavigationFieldsetPrototype()
    {
        $navigationElement = MultipageFormNavFieldset::create();
        $navigationElement->setCancelEnabled(false);
        $nextButton = $navigationElement->getNextButton();
        $prevButton = $navigationElement->getPreviousButton();
        $submitButton = $navigationElement->getSubmitButton();
        $confirmButton = $navigationElement->getConfirmButton();
        $cancelButton = $navigationElement->getCancelButton();

        // ajouts de classes CSS
        $nextButton->setAttribute('class', $nextButton->getAttribute('class') . ' btn btn-primary');

        $prevButton->setAttribute('class', $prevButton->getAttribute('class') . ' btn btn-primary');
        $submitButton->setAttribute('class', $submitButton->getAttribute('class') . ' btn btn-success');
        $submitButton->setValue('Enregistrer');
        $confirmButton->setAttribute('class', $confirmButton->getAttribute('class') . ' btn btn-success');
        $cancelButton->setAttribute('class', $confirmButton->getAttribute('class') . ' visually-hidden');

        return $navigationElement;
    }
}