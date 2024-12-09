<?php

namespace Depot\Rule;

use Application\Rule\PluginManager\RulePluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SuppressionAttestationsRequiseRuleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SuppressionAttestationsRequiseRule
    {
        /** @var array $config */
        $config = $container->get('config');

        $options = array_merge(
            $config['diffusion']['rules'][$requestedName] ?? [],
            $options ?: []
        );

        $rule = new SuppressionAttestationsRequiseRule($options);

        /** @var RemiseExemplairePapierRule $autorisationDiffusionRule */
        $autorisationDiffusionRule = $container->get(RulePluginManager::class)->get(RemiseExemplairePapierRule::class);
        $rule->setRemiseExemplairePapierRule($autorisationDiffusionRule);

        return $rule;
    }
}