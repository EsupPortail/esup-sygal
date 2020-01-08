<?php

namespace Soutenance\Form\AdresseSoutenance;

use Zend\Form\FormElementManager;

class AdresseSoutenanceFormFactory {
    /**
     * @param FormElementManager $manager
     * @return AdresseSoutenanceForm
     */
    public function __invoke(FormElementManager $manager)
    {
        /** @var AdresseSoutenanceHydrator $hydrator */
        $hydrator = $manager->getServiceLocator()->get('HydratorManager')->get(AdresseSoutenanceHydrator::class);

        /** @var AdresseSoutenanceForm $form */
        $form = new AdresseSoutenanceForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}