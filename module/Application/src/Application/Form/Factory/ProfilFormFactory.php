<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\ProfilHydrator;
use Application\Form\ProfilForm;
use Zend\Form\FormElementManager;

class ProfilFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        /** @var ProfilHydrator $hydrator */
        $hydrator = $formElementManager->getServiceLocator()->get('HydratorManager')->get(ProfilHydrator::class);

        /** @var ProfilForm $form */
        $form = new ProfilForm();
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}