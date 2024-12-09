<?php

namespace Feature\Entity\Db\Service;

trait FeatureServiceAwareTrait
{
    protected FeatureService $featureService;

    public function setFeatureService(FeatureService $featureService): void
    {
        $this->featureService = $featureService;
    }
}