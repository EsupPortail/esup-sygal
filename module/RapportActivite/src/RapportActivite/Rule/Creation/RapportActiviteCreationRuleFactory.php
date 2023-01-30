<?php

namespace RapportActivite\Rule\Creation;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteCreationRuleFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteCreationRule
    {
        $rule = new RapportActiviteCreationRule();

        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $rule->setRapportActiviteService($rapportActiviteService);

        return $rule;
    }
}