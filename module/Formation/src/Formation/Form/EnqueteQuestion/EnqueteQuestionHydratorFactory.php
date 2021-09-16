<?php

namespace Formation\Form\EnqueteQuestion;

use Formation\Service\EnqueteCategorie\EnqueteCategorieService;
use Interop\Container\ContainerInterface;

class EnqueteQuestionHydratorFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EnqueteCategorieService $enqueteCategorieService
         */
        $enqueteCategorieService = $container->get(EnqueteCategorieService::class);

        $hydrator = new EnqueteQuestionHydrator();
        $hydrator->setEnqueteCategorieService($enqueteCategorieService);
        return $hydrator;
    }
}