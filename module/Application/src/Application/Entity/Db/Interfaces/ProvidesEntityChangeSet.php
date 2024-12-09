<?php

namespace Application\Entity\Db\Interfaces;

use Doctrine\ORM\EntityManager;

trait ProvidesEntityChangeSet
{
    /**
     * Calcule et retourne le "changeset" de cette instance.
     *
     * Exemple de résultat :
     *
     *      [
     *          'autorisMel' => [
     *              0 => 2 // ancienne valeur
     *              1 => 1 // nouvelle valeur
     *          ],
     *           'motif' => [
     *               0 => null // ancienne valeur
     *               1 => "Coucou" // nouvelle valeur
     *           ]
     *      ]
     *
     * @see \Doctrine\ORM\UnitOfWork::getEntityChangeSet()
     */
    public function getEntityChangeSet(EntityManager $entityManager): array
    {
        $metadata = $entityManager->getClassMetadata(static::class);
        $uow = $entityManager->getUnitOfWork();
        $uow->recomputeSingleEntityChangeSet($metadata, $this);

        return $uow->getEntityChangeSet($this);
    }

    /**
     * Calcule et retourne le "changeset" de cette instance, concernant la seule propriété spécifiée.
     *
     * Exemple de résultat :
     *
     *       [
     *          0 => null // ancienne valeur
     *          1 => "Coucou" // nouvelle valeur
     *       ]
     *
     * @see \Doctrine\ORM\UnitOfWork::getEntityChangeSet()
     */
    public function getEntityChangeSetForProperty(EntityManager $entityManager, string $property): array
    {
        $changeset = $this->getEntityChangeSet($entityManager);

        return $changeset[$property] ?? [];
    }
}