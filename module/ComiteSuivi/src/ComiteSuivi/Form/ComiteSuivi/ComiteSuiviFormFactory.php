<?php

namespace ComiteSuivi\Form\ComiteSuivi;

use Zend\Form\FormElementManager;

class ComiteSuiviFormFactory {

    /**
     * @param FormElementManager $manager
     * @return ComiteSuiviForm
     */
    public function __invoke(FormElementManager $manager)
    {
        /** @var ComiteSuiviHydrator $hydrator */
        $hydrator = $manager->getServiceLocator()->get('HydratorManager')->get(ComiteSuiviHydrator::class);

        /** @var ComiteSuiviForm $form */
        $form = new ComiteSuiviForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}