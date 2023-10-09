<?php

namespace Admission\Service\Validation;

use Admission\Entity\Db\Validation;
use Admission\Entity\Db\Repository\ValidationRepository;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class ValidationService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return ValidationRepository
     */
    public function getRepository(): ValidationRepository
    {
        /** @var ValidationRepository $repo */
        $repo = $this->entityManager->getRepository(Validation::class);

        return $repo;
    }

    /**
     * @param Validation $validation
     * @return Validation
     */
    public function create(Validation $validation) : Validation
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $validation->setHistoModification($date);
            $validation->setHistoModificateur($user);
            $this->getEntityManager()->persist($validation);
            $this->getEntityManager()->flush($validation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Validation");
        }

        return $validation;
    }



    /**
     * @param Validation $validation
     * @return Validation
     */
    public function update(Validation $validation)  :Validation
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $validation->setHistoModification($date);
            $validation->setHistoModificateur($user);
            $this->getEntityManager()->flush($validation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Validation");
        }

        return $validation;
    }

    /**
     * @param Validation $validation
     * @return Validation
     */
    public function historise(Validation $validation)  :Validation
    {
        try {
            $validation->historiser();
            $this->getEntityManager()->flush($validation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Validation");
        }
        return $validation;
    }

    /**
     * @param Validation $validation
     * @return Validation
     */
    public function restore(Validation $validation)  :Validation
    {
        try {
            $validation->dehistoriser();
            $this->getEntityManager()->flush($validation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Validation");
        }
        return $validation;
    }

    /**
     * @param Validation $validation
     * @return Validation
     */
    public function delete(Validation $validation) : Validation
    {
        try {
            $this->getEntityManager()->remove($validation);
            $this->getEntityManager()->flush($validation);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Validation");
        }

        return $validation;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Validation
     */
    public function getRequestedValidation(AbstractActionController $controller, string $param='Validation')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Validation $validation */
        $validation = $this->getRepository()->find($id);
        return $validation;
    }
}