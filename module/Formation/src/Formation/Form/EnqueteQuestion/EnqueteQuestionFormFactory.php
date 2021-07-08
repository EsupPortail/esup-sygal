<?php

namespace Formation\Form\EnqueteQuestion;

use Interop\Container\ContainerInterface;

class EnqueteQuestionFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        /** @var EnqueteQuestionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(EnqueteQuestionHydrator::class);

        $form = new EnqueteQuestionForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}