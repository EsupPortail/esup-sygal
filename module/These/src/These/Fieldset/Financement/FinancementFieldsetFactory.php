<?php

namespace These\Fieldset\Financement;

use Application\Service\Financement\FinancementService;
use Interop\Container\ContainerInterface;

class FinancementFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementFieldset
    {
        $fieldset = new FinancementFieldset();
        /** @var FinancementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(FinancementHydrator::class);
        $fieldset->setHydrator($hydrator);

        $financementService = $container->get(FinancementService::class);
        $origines = $financementService->findOriginesFinancements("libelleLong");
        $fieldset->setOrigineFinancementsPossibles($origines);

        /** @var FinancementService $financementService */
        $financementService = $container->get(FinancementService::class);
        $fieldset->setObject($financementService->newFinancement());

        return $fieldset;
    }
}