<?php

namespace Application\Rule\PluginManager;

use Application\Rule\RuleInterface;
use Feature\FeatureAwareInterface;
use Feature\InjectsFeatures;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\Stdlib\ArrayUtils;

class RulePluginManager extends AbstractPluginManager
{
    use InjectsFeatures;

    protected $instanceOf = RuleInterface::class;
    protected string $configKey = 'config';

    /**
     * Config pour chaque classe de rule.
     * @var array<string, array>
     */
    protected array $config = [];

    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        $this->config = $config[$this->configKey] ?? [];
        unset($config[$this->configKey]);

        parent::__construct($configInstanceOrParentLocator, $config);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function get($name, ?array $options = null): RuleInterface
    {
        $options = ArrayUtils::merge(
            $this->config[$name] ?? [],
                (array)$options
        );

        /** @var \Application\Rule\RuleInterface $rule */
        $rule = parent::get($name, $options);

        // injection éventuelle des features
        $this->injectFeatures($rule, $options ?: []);

        return $rule;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function injectFeatures(RuleInterface $rule, array $options): void
    {
        // injection éventuelle des features
        if ($rule instanceof FeatureAwareInterface) {
            $features = $options['features'] ?? [];
            $this->injectFeaturesFromContainer($features, $rule, $this->creationContext);
        }
    }
}