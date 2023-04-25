<?php

namespace Application\Service\Validation;

use Application\Entity\Db\TypeValidation;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;

class TypeValidationService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(TypeValidation::class);
        return $repo;
    }
}