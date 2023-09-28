<?php

namespace Admission\Form\Etudiant;

use Admission\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Fieldset\Justificatifs\ValidationFieldset;
use Admission\Fieldset\Financement\FinancementFieldset;
use Admission\Fieldset\Inscription\InscriptionFieldset;
use Laminas\Form\Element\Submit;
use UnicaenApp\Form\Fieldset\MultipageFormNavFieldset;
use UnicaenApp\Form\MultipageForm;

class EtudiantForm extends MultipageForm
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

        $validationFieldset = $this->getFormFactory()->getFormElementManager()->get(ValidationFieldset::class);
        $validationFieldset->setName("validation");
        $this->add($validationFieldset);

        $this->add((new Submit('save_and_come_back_later'))
            ->setValue("Enregistrer et continuer plus tard")
            ->setAttribute('class', 'btn btn-warning btn-lg')
        );

        $this->add((new Submit('save_and_continue'))
            ->setValue("Étape suivante et enregistrer")
            ->setAttribute('class', 'btn btn-success btn-lg')
        );

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
        $prevButton->setAttribute('class', $prevButton->getAttribute('class') . ' btn btn-primary');
        $cancelButton->setAttribute('class', $cancelButton->getAttribute('class') . ' btn btn-danger');
        $submitButton->setAttribute('class', $submitButton->getAttribute('class') . ' btn btn-success');
        $confirmButton->setAttribute('class', $confirmButton->getAttribute('class') . ' btn btn-success');

        return $navigationElement;
    }
}