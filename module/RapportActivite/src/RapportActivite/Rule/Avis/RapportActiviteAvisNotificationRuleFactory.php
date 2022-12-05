<?php

namespace RapportActivite\Rule\Avis;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;

class RapportActiviteAvisNotificationRuleFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisNotificationRule
    {
        $rapportAvisService = $container->get(RapportActiviteAvisService::class);

        $rule = new RapportActiviteAvisNotificationRule();
        $rule->setRapportActiviteAvisService($rapportAvisService);

        return $rule;
    }
}