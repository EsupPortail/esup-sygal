<?php

namespace Admission\Rule\Operation\Notification;

use Admission\Rule\Email\ExtractionEmailRule;
use Admission\Rule\Operation\AdmissionOperationRule;
use Application\Service\Role\RoleService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OperationAttendueNotificationRuleFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): OperationAttendueNotificationRule
    {
        $rule = new OperationAttendueNotificationRule();

        /** @var AdmissionOperationRule $admissionOperationRule */
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);
        $rule->setAdmissionOperationRule($admissionOperationRule);

        $extractionEmailRule = $container->get(ExtractionEmailRule::class);
        $rule->setExtractionEmailRule($extractionEmailRule);

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $rule->setRoleService($roleService);

        return $rule;
    }
}