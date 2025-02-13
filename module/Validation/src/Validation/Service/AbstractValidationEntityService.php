<?php

namespace Validation\Service;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;
use Application\Service\UserContextServiceAwareTrait;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Individu\Service\IndividuServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Validation\Entity\Db\AbstractValidationEntity;

class AbstractValidationEntityService extends BaseService
{
    use UserContextServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use IndividuServiceAwareTrait;

    protected string $validationEntityClass;

    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository($this->validationEntityClass);
        return $repo;
    }

    public function historiser(AbstractValidationEntity $validation): AbstractValidationEntity
    {
        $user = $this->userContextService->getIdentityDb();
        $date = new DateTime();

        $validation->getValidation()->setHistoDestructeur($user);
        $validation->getValidation()->setHistoDestruction($date);
        $validation->setHistoDestructeur($user);
        $validation->setHistoDestruction($date);

        $this->saveValidation($validation);

        return $validation;
    }

    public function saveValidation(AbstractValidationEntity $validationHDR): void
    {
        $this->entityManager->beginTransaction();
        try {
            $this->validationService->saveValidation($validationHDR->getValidation());

            if (!$validationHDR->getId()) {
                $this->entityManager->persist($validationHDR);
            }
            $this->entityManager->flush($validationHDR);
            $this->entityManager->commit();
        } catch (ORMException $e) {
            $this->entityManager->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement de la validation en bdd", null, $e);
        }
    }
}