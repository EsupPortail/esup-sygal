<?php

namespace RapportActivite\Rule\Operation;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\Operation\RapportActiviteOperationService;
use UnicaenOperation\Service\OperationService;

class RapportActiviteOperationRuleFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteOperationRule
    {
        $config = $container->get('config')['rapport-activite']['operations'];

        $rule = new RapportActiviteOperationRule($config);

        /** @var \RapportActivite\Service\Operation\RapportActiviteOperationService $rapportActiviteOperationService */
        $rapportActiviteOperationService = $container->get(RapportActiviteOperationService::class);
        $rule->setRapportActiviteOperationService($rapportActiviteOperationService);

        return $rule;
    }
}