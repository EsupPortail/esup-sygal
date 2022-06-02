<?php

namespace RapportActivite\Rule\Validation;

use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Validation\RapportActiviteValidationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;

class RapportActiviteValidationRuleFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationRule
    {
        /** @var RapportActiviteAvisService $rapportAvisService */
        $rapportAvisService = $container->get(RapportActiviteAvisService::class);

        $rule = new RapportActiviteValidationRule();
        $rule->setRapportActiviteAvisService($rapportAvisService);

        return $rule;
    }
}