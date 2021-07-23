<?php

namespace Formation\Form\EnqueteQuestion;

use Interop\Container\ContainerInterface;

class EnqueteQuestionHydratorFactory {

    public function __invoke(ContainerInterface $container)
    {
        $hydrator = new EnqueteQuestionHydrator();
        return $hydrator;
    }
}