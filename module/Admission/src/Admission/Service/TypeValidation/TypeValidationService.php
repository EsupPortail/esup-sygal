<?php

namespace Admission\Service\TypeValidation;

use Admission\Entity\Db\TypeValidation;
use Admission\Entity\Db\Repository\TypeValidationRepository;
use Application\Service\BaseService;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use UnicaenApp\Exception\RuntimeException;

class TypeValidationService extends BaseService
{
    /**
     * @return TypeValidationRepository
     * @throws NotSupported
     */
    public function getRepository(): TypeValidationRepository
    {
        /** @var TypeValidationRepository $repo */
        $repo = $this->entityManager->getRepository(TypeValidation::class);

        return $repo;
    }

    /**
     * @param TypeValidation $typeTypeValidation
     * @return TypeValidation
     */
    public function create(TypeValidation $typeTypeValidation) : TypeValidation
    {
        try {
            $this->getEntityManager()->persist($typeTypeValidation);
            $this->getEntityManager()->flush($typeTypeValidation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un TypeValidation");
        }

        return $typeTypeValidation;
    }

    /**
     * @param TypeValidation $typeTypeValidation
     * @return TypeValidation
     */
    public function update(TypeValidation $typeTypeValidation)  :TypeValidation
    {
        try {
            $this->getEntityManager()->flush($typeTypeValidation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un TypeValidation");
        }

        return $typeTypeValidation;
    }

    /**
     * @param TypeValidation $typeTypeValidation
     * @return TypeValidation
     */
    public function delete(TypeValidation $typeTypeValidation) : TypeValidation
    {
        try {
            $this->getEntityManager()->remove($typeTypeValidation);
            $this->getEntityManager()->flush($typeTypeValidation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un TypeValidation");
        }

        return $typeTypeValidation;
    }

    /**
     * Fetch le type de validation spécifié par son code.
     *
     * @param string $code
     * @return TypeValidation
     */
    public function findTypeValidationByCode(string $code): TypeValidation
    {
        /** @var TypeValidation $type */
        $type = $this->getRepository()->findOneBy(['code' => $code]);
        if ($type === null) {
            throw new RuntimeException("Type de validation introuvable avec ce code : " . $code);
        }

        return $type;
    }

}