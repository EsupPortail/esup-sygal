<?php

namespace Soutenance\Form\Justificatif;

use Zend\Form\FormElementManager;

class JustificatifFormFactory {

    public function __invoke(FormElementManager $container)
    {
        /**
         * @var JusticatifHydrator $hydrator
         */
        $hydrator = $container->getServiceLocator()->get('HydratorManager')->get(JusticatifHydrator::class);

        $form = new JustificatifForm();
        $form->setHydrator($hydrator);
        $form->init();
        return $form;
    }
}