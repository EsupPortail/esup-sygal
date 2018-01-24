<?php

namespace Application\Service\Validation;

use Application\Entity\Db\Repository\TheseRepository;
use Application\Entity\Db\TypeValidation;
use Application\Service\BaseService;

class TypeValidationService extends BaseService
{
    /**
     * @return TheseRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(TypeValidation::class);
    }
}