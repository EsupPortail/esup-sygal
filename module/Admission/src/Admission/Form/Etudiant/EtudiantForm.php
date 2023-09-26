<?php

namespace Admission\Form\Etudiant;

use Admission\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Fieldset\Justificatifs\ValidationFieldset;
use Admission\Fieldset\Financement\FinancementFieldset;
use Admission\Fieldset\Inscription\InscriptionFieldset;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use UnicaenApp\Form\Fieldset\MultipageFormNavFieldset;
use UnicaenApp\Form\MultipageForm;

class EtudiantForm extends MultipageForm
//class EtudiantForm extends Form
{
//    public function __construct($name = null, $options = array())
//    {
//        parent::__construct($name, $options);
//
//        $this->add(new EtudiantFieldset("infosEtudiant"))
//            ->add(new Csrf('csrf'))
//            ->add(new Submit('save_and_come_back_later', array('label'=>"Enregistrer et continuer plus tard", 'class' => 'btn btn-success btn-lg')))
//            ->add(new Submit('save_and_continue', array('label'=>"Étape suivante et enregistrer", 'class' => 'btn btn-success btn-lg')));
//    }


    public function init()
    {
        parent::init();

        $this->add([
            'name' => "etudiant",
            'type' => EtudiantFieldset::class,
        ]);

        $this->add([
            'name' => "inscription",
            'type' => InscriptionFieldset::class,
        ]);

        $this->add([
            'name' => "financement",
            'type' => FinancementFieldset::class,
        ]);

        $this->add([
            'name' => "validation",
            'type' => ValidationFieldset::class,
        ]);

//        $etudiantFieldset = $this->getFormFactory()->getFormElementManager()->get(EtudiantFieldset::class);
//        $etudiantFieldset->setLabel("Informations concernant l'étudiant");
//        $this->add($etudiantFieldset)->setName("etudiant");
//
//        /** @var InscriptionFieldset $inscriptionFieldset */
//        $inscriptionFieldset = $this->getFormFactory()->getFormElementManager()->get(InscriptionFieldset::class);
//        $inscriptionFieldset->setLabel("Inscription");
//        $this->add($inscriptionFieldset)->setName("inscription");

        $this->add((new Submit('save_and_come_back_later'))
            ->setValue("Enregistrer et continuer plus tard")
            ->setAttribute('class', 'btn btn-warning btn-lg')
        );

        $this->add((new Submit('save_and_continue'))
            ->setValue("Étape suivante et enregistrer")
            ->setAttribute('class', 'btn btn-success btn-lg')
        );

        $this->addConfirmFieldset();

//        $this->add(new Csrf('security'));
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
        $prevButton->setAttribute('class', $prevButton->getAttribute('class') . ' btn btn-default');
        $cancelButton->setAttribute('class', $cancelButton->getAttribute('class') . ' btn btn-warning');
        $submitButton->setAttribute('class', $submitButton->getAttribute('class') . ' btn btn-success');
        $confirmButton->setAttribute('class', $confirmButton->getAttribute('class') . ' btn btn-success');

        return $navigationElement;
    }
}