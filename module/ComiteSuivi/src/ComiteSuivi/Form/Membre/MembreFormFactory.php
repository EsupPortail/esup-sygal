<?php

namespace ComiteSuivi\Form\Membre;

use Zend\Form\FormElementManager;

class MembreFormFactory {

    /**
     * @param FormElementManager $manager
     * @return MembreForm
     */
    public function __invoke(FormElementManager $manager)
    {
        /**
         * @var MembreHydrator $hydrator
         */
        $hydrator = $manager->getServiceLocator()->get('HydratorManager')->get(MembreHydrator::class);

        /** @var MembreForm $form */
        $form = new MembreForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}