<?php

namespace Application\Service;

use Application\Constants;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;
use Webmozart\Assert\Assert;

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
     * Calcule le diff (en terme de valeur d'attributs) entre 2 entités.
     *
     * @param object $entity1 1ere entité
     * @param object $entity2 2eme entité
     * @param array|null $fieldNames Champs à prendre en compte, sinon ils sont tous pris en compte
     *
     * @return array Ex : [ 'prenom1' => ['Paul', 'Paule'], 'nomUsuel' => ['Hochon', 'Hauchon'] ]
     */
    protected function computeEntityDiff(object $entity1, object $entity2, ?array $fieldNames = null): array
    {
        $metadata = $this->entityManager->getClassMetadata($this->getRepository()->getClassName());

        if ($fieldNames === null) {
            $fieldNames = $metadata->getFieldNames();
        }
        Assert::notEmpty($fieldNames);

        $stringify = function($item) {
            if ($item instanceof DateTime) {
                return $item->format(Constants::DATETIME_FORMAT);
            }
            return (string)$item;
        };

        $diff = [];
        foreach ($fieldNames as $fieldName) {
            $individuFieldValue = $metadata->getFieldValue($entity1, $fieldName);
            $individuFromApprenantFieldValue = $metadata->getFieldValue($entity2, $fieldName);
            if ($individuFieldValue <> $individuFromApprenantFieldValue) {
                $diff[$fieldName] = [
                    $stringify($individuFieldValue),
                    $stringify($individuFromApprenantFieldValue)
                ];
            }
        }

        return $diff;
    }
}