<?php

namespace Feature;

use Feature\Entity\FeatureInterface;
use Feature\PluginManager\FeaturePluginManager;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

trait InjectsFeatures
{
    /**
     * Demande au service manager des fonctionnalités (spécifiées par leur classe)
     * puis les injecte dans une instance "feature aware".
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function injectFeaturesFromContainer(array                 $features,
                                                   FeatureAwareInterface $instance,
                                                   ContainerInterface    $container): void
    {
        /** @var FeaturePluginManager $featureManager */
        $featureManager = $container->get(FeaturePluginManager::class);

        // 2 formats possibles pour le tableau des features :
        //   - [ int => string ]
        //   - [ string => array ]
        foreach ($features as $key => $value) {
            if ($value instanceof FeatureInterface) {
                $instance->addFeature($value);
                continue;
            }
            if (is_string($key)) {
                $class = $key;
                $options = $value;
                Assert::isArray($options);
            } else {
                $class = $value;
                $options = [];
            }
            /** @var \Feature\Entity\FeatureInterface $feature */
            $feature = $featureManager->get($class, $options);
            $instance->addFeature($feature);
        }
    }
}