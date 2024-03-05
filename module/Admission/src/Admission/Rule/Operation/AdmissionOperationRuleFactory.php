<?php

namespace Admission\Rule\Operation;

use Admission\Config\ModuleConfig;
use Admission\Service\Operation\AdmissionOperationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionOperationRuleFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionOperationRule
    {
        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);

        $rule = new AdmissionOperationRule($moduleConfig->getOperationsConfig());

        /** @var AdmissionOperationService $admissionOperationService */
        $admissionOperationService = $container->get(AdmissionOperationService::class);
        $rule->setAdmissionOperationService($admissionOperationService);

        return $rule;
    }
}