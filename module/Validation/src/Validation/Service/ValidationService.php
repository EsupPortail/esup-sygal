<?php

namespace Validation\Service;

use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use UnicaenApp\Exception\RuntimeException;
use Validation\Entity\Db\Repository\ValidationRepository;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\Validation;

class ValidationService extends BaseService
{
    use UserContextServiceAwareTrait;

    public function getRepository(): ValidationRepository
    {
        /** @var ValidationRepository $repo */
        $repo = $this->entityManager->getRepository(Validation::class);
        return $repo;
    }

    public function saveValidation(Validation $v): void
    {
        try {
            if (!$v->getId()) {
                $this->entityManager->persist($v);
            }
            $this->entityManager->flush($v);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement de la validation en bdd", null, $e);
        }
    }

    /**
     * Fetch le type de validation spécifié par son code.
     */
    public function findTypeValidationByCode(string $code): TypeValidation
    {
        /** @var TypeValidation $type */
        $type = $this->entityManager->getRepository(TypeValidation::class)->findOneBy(['code' => $code]);
        if ($type === null) {
            throw new RuntimeException("Type de validation introuvable avec ce code : " . $code);
        }

        return $type;
    }

    /**
     * Fetch le type de validation spécifié par son id.
     */
    public function findTypeValidationById(int $id): TypeValidation
    {
        /** @var TypeValidation $type */
        $type = $this->entityManager->getRepository(TypeValidation::class)->find($id);
        if ($type === null) {
            throw new RuntimeException("Type de validation introuvable avec cet id : " . $id);
        }

        return $type;
    }

}