<?php

namespace Feature\Entity\Db\Service;

use Application\Service\BaseService;
use Feature\Entity\Db\Feature;
use Feature\Entity\Db\Repository\FeatureRepository;

class FeatureService extends BaseService
{
    public function getRepository(): FeatureRepository
    {
        return $this->entityManager->getRepository(Feature::class);
    }
}