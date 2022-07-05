<?php

namespace Formation\Form\EnqueteQuestion;

use Formation\Service\EnqueteCategorie\EnqueteCategorieService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EnqueteQuestionHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteQuestionHydrator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : EnqueteQuestionHydrator
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