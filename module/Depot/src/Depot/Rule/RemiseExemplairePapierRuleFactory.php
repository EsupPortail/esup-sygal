<?php

namespace Depot\Rule;

use Feature\InjectsFeatures;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RemiseExemplairePapierRuleFactory implements FactoryInterface
{
    use InjectsFeatures;

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RemiseExemplairePapierRule
    {
//        $features = $options['features'] ?? [];
//        unset($options['features']);

        $rule = new RemiseExemplairePapierRule($options);

//        $this->injectFeaturesFromContainer($features, $rule, $container);

        return $rule;
    }
}