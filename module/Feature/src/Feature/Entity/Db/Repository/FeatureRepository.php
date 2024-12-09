<?php

namespace Feature\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Feature\Entity\Db\Feature;

class FeatureRepository extends DefaultEntityRepository
{
    public function findOneByCode(string $code): ?Feature
    {
        return $this->findOneBy(['code' => $code]);
    }

}