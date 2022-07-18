<?php

namespace Formation\Form\EnqueteQuestion;

use Formation\Service\EnqueteCategorie\EnqueteCategorieService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EnqueteQuestionFormFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteQuestionForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)  :EnqueteQuestionForm
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