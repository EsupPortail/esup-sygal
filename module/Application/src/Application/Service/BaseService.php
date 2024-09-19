<?php

namespace Application\Service;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;

abstract class BaseService implements EntityManagerAwareInterface
{
    use EntityManagerAwareTrait;
    use MessageAwareTrait;

    /**
     * @return DefaultEntityRepository
     */
    abstract public function getRepository();

    /**
     * Proxy method.
     *
     * @see EntityManager::beginTransaction()
     */
    public function beginTransaction()
    {
        $this->getEntityManager()->beginTransaction();
    }

    /**
     * Proxy method.
     *
     * @see EntityManager::commit()
     */
    public function commit()
    {
        $this->getEntityManager()->commit();
    }

    /**
     * Proxy method.
     *
     * @see EntityManager::rollback()
     */
    public function rollback()
    {
        $this->getEntityManager()->rollback();
    }

    /**
     * Calcule le "changeset" d'une entité (propriétés dont la valeur a changé)
     * si on lui applique l'update spécifié.
     *
     * @param object $entity Entity concernée
     * @param array $fields Champs à prendre en compte, sinon ils sont tous pris en compte
     * @param callable $updateEntityCallable Callable de l'update à réaliser
     * @return array<string, array<mixed, mixed>>
     */
    protected function computeDiffForUpdate(object $entity, callable $updateEntityCallable, array $fields = []): array
    {
        $entityManager = $this->getEntityManager();
        $uow = $entityManager->getUnitOfWork();
        if ($uow->getEntityChangeSet($entity)) {
            throw new InvalidArgumentException("Opération impossible car l'entité est en cours de modification");
        }

        // application provisoire de l'update UNIQUEMENT pour pouvoir calculer le "changeset" Doctrine
        $updateEntityCallable($entity);

        // calcul du changeset
        $entityClass = get_class($entity);
        $metadata = $entityManager->getClassMetadata($entityClass);
        $uow->recomputeSingleEntityChangeSet($metadata, $entity);
        $changeset = $uow->getEntityChangeSet($entity);

        // refresh de l'entité pour annuler l'update
        try {
            $entityManager->refresh($entity);
        } catch (Exception $e) {
            throw new RuntimeException("Refresh impossible ! " . $e->getMessage(), null, $e);
        }

        // mise en forme du résultat
        if (!$fields) {
            $fields = $metadata->getFieldNames();
        }
        $fieldsWithChanges = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $changeset)) {
                continue;
            }
            $oldValue = $changeset[$field][0];
            $newValue = $changeset[$field][1];
            if ($oldValue <> $newValue) {
                $fieldsWithChanges[$field] = [$oldValue, $newValue];
            }
        }

        return $fieldsWithChanges;
    }
}