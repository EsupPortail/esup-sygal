<?php

namespace Application\Form\Factory;

use Application\Form\MailConfirmationForm;
use Application\Form\Hydrator\MailConfirmationHydrator;
use Zend\Form\FormElementManager;

class MailConfirmationFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var MailConfirmationHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('MailConfirmationHydrator');

        $form = new MailConfirmationForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}