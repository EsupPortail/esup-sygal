<?php

namespace InscriptionAdministrative\Rule\Reception\StatutAnnulee;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StatutAnnuleeRuleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): StatutAnnuleeRule
    {
        $rule = new StatutAnnuleeRule();

        return $rule;
    }
}