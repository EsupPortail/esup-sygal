<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\MailConfirmationHydrator;
use Application\Form\MailConfirmationForm;
use Interop\Container\ContainerInterface;

class MailConfirmationFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var MailConfirmationHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('MailConfirmationHydrator');

        $form = new MailConfirmationForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}