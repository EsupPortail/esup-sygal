<?php

namespace Application\Form\Factory;

use Application\Form\EtablissementForm;
use Application\Form\Hydrator\EtablissementHydrator;
use Zend\Form\FormElementManager;

class EtablissementFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var EtablissementHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('EtablissementHydrator');

        $form = new EtablissementForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}