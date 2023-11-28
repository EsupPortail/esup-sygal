<?php

namespace Admission\Service\TypeValidation;

use Admission\Entity\Db\TypeValidation;
use Admission\Entity\Db\Repository\TypeValidationRepository;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\ORMException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class TypeValidationService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

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
    public function historise(TypeValidation $typeTypeValidation)  :TypeValidation
    {
        try {
            $typeTypeValidation->historiser();
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
    public function restore(TypeValidation $typeTypeValidation)  :TypeValidation
    {
        try {
            $typeTypeValidation->dehistoriser();
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
     * @param AbstractActionController $controller
     * @param string $param
     * @return TypeValidation
     */
    public function getRequestedTypeValidation(AbstractActionController $controller, string $param='TypeValidation')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var TypeValidation $typeTypeValidation */
        $typeTypeValidation = $this->getRepository()->find($id);
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