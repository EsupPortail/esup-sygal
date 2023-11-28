<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\TypeValidation;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class TypeValidationRepository extends DefaultEntityRepository{
    /**
     * Fetch le type de validation spécifié par son id.
     *
     * @param int $id
     * @return TypeValidation
     */
    public function findTypeValidationById(int $id): TypeValidation
    {
        /** @var TypeValidation $type */
        $type = $this->find($id);
        if ($type === null) {
            throw new \RuntimeException("Type de validation introuvable avec cet id : " . $id);
        }

        return $type;
    }
}