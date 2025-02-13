<?php

namespace RapportActivite\Rule\Operation\Notification;

use Application\Service\Role\RoleService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use Acteur\Service\ActeurThese\ActeurTheseService;

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

        /** @var \Acteur\Service\ActeurThese\ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $rule->setActeurTheseService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $rule->setApplicationRoleService($roleService);

        return $rule;
    }
}