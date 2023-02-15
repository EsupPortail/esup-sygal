<?php

namespace RapportActivite\Rule\Operation;

use Psr\Container\ContainerInterface;
use RapportActivite\Config\ModuleConfig;
use RapportActivite\Service\Operation\RapportActiviteOperationService;

class RapportActiviteOperationRuleFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteOperationRule
    {
        /** @var \RapportActivite\Config\ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);

        $rule = new RapportActiviteOperationRule($moduleConfig->getOperationsConfig());

        /** @var \RapportActivite\Service\Operation\RapportActiviteOperationService $rapportActiviteOperationService */
        $rapportActiviteOperationService = $container->get(RapportActiviteOperationService::class);
        $rule->setRapportActiviteOperationService($rapportActiviteOperationService);

        return $rule;
    }
}