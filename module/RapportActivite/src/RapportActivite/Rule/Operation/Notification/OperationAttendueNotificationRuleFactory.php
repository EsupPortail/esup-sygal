<?php

namespace RapportActivite\Rule\Operation\Notification;

use Application\Service\Role\RoleService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use These\Service\Acteur\ActeurService;

class OperationAttendueNotificationRuleFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): OperationAttendueNotificationRule
    {
        $rapportAvisService = $container->get(RapportActiviteAvisService::class);

        $rule = new OperationAttendueNotificationRule();
        $rule->setRapportActiviteAvisService($rapportAvisService);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $rule->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        /** @var \These\Service\Acteur\ActeurService $acteurService */
        $acteurService = $container->get(ActeurService::class);
        $rule->setActeurService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $rule->setRoleService($roleService);

        return $rule;
    }
}