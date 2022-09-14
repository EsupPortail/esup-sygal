<?php

namespace These\Form\Diffusion;

use These\Form\Diffusion\DiffusionTheseForm;
use These\Form\Diffusion\DiffusionHydrator;
use Interop\Container\ContainerInterface;
use UnicaenApp\Message\MessageService;

class DiffusionTheseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var \These\Form\Diffusion\DiffusionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('DiffusionHydrator');

        /** @var MessageService $messageService */
        $messageService = $container->get('MessageService');

        $form = new DiffusionTheseForm();
        $form->setHydrator($hydrator);
        $form->setMessageService($messageService);

        return $form;
    }
}