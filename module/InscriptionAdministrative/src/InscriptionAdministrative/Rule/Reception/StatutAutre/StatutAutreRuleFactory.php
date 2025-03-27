<?php

namespace InscriptionAdministrative\Rule\Reception\StatutAutre;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StatutAutreRuleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): StatutAutreRule
    {
        $rule = new StatutAutreRule();

        return $rule;
    }
}