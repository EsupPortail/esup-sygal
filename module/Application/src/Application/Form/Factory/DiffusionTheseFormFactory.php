<?php

namespace Application\Form\Factory;

use Application\Form\DiffusionTheseForm;
use Application\Form\Hydrator\DiffusionHydrator;
use UnicaenApp\Message\MessageService;
use Zend\Form\FormElementManager;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 29/04/16
 * Time: 09:20
 */
class DiffusionTheseFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var DiffusionHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('DiffusionHydrator');

        /** @var MessageService $messageService */
        $messageService = $formElementManager->getServiceLocator()->get('MessageService');

        $form = new DiffusionTheseForm();
        $form->setHydrator($hydrator);
        $form->setMessageService($messageService);

        return $form;
    }
}