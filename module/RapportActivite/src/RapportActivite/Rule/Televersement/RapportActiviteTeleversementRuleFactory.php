<?php

namespace RapportActivite\Rule\Televersement;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteTeleversementRuleFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteTeleversementRule
    {
        $rule = new RapportActiviteTeleversementRule();

        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $rule->setRapportActiviteService($rapportActiviteService);

        return $rule;
    }
}