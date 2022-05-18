<?php

namespace These\Form\Factory;

use These\Form\DiffusionTheseForm;
use These\Form\Hydrator\DiffusionHydrator;
use Interop\Container\ContainerInterface;
use UnicaenApp\Message\MessageService;

class DiffusionTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var DiffusionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('DiffusionHydrator');

        /** @var MessageService $messageService */
        $messageService = $container->get('MessageService');

        $form = new DiffusionTheseForm();
        $form->setHydrator($hydrator);
        $form->setMessageService($messageService);

        return $form;
    }
}