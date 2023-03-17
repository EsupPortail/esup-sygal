<?php

namespace Admission\Rule\Email;

use Application\Service\Role\RoleService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ExtractionEmailRuleFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ExtractionEmailRule
    {
        $rule = new ExtractionEmailRule();

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $rule->setApplicationRoleService($roleService);

        return $rule;
    }
}