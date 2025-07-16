<?php

namespace Feature\Entity\Db\Service;

interface FeatureServiceAwareInterface
{
    public function setFeatureService(FeatureService $featureService);
}