<?php

namespace Formation\Form\EnqueteQuestion;

use Formation\Service\EnqueteCategorie\EnqueteCategorieService;
use Interop\Container\ContainerInterface;

class EnqueteQuestionFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EnqueteCategorieService $enqueteCategorieService
         */
        $enqueteCategorieService = $container->get(EnqueteCategorieService::class);

        /** @var EnqueteQuestionHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(EnqueteQuestionHydrator::class);

        $form = new EnqueteQuestionForm();
        $form->setEnqueteCategorieService($enqueteCategorieService);
        $form->setHydrator($hydrator);
        return $form;
    }
}