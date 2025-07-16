<?php

namespace Feature\PluginManager;

trait FeaturePluginManagerAwareTrait
{
    protected FeaturePluginManager $featurePluginManager;

    public function setFeaturePluginManager(FeaturePluginManager $featurePluginManager): void
    {
        $this->featurePluginManager = $featurePluginManager;
    }
}