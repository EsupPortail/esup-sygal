<?php

namespace Admission\Service\Individu;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Individu;
use Admission\Entity\Db\Repository\IndividuRepository;
use Admission\Entity\Db\Repository\ValidationRepository;
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

class IndividuService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return IndividuRepository
     */
    public function getRepository(): IndividuRepository
    {
        /** @var IndividuRepository $repo */
        $repo = $this->entityManager->getRepository(Individu::class);

        return $repo;
    }

    /**
     * @param Individu $individu
     * @param Admission $admission
     * @return Individu
     */
    public function create(Individu $individu, Admission $admission) : Individu
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $individu->setHistoModification($date);
            $individu->setHistoModificateur($user);
            $this->getEntityManager()->persist($admission);
            $this->getEntityManager()->persist($individu);
            $this->getEntityManager()->flush($individu);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Individu");
        }

        return $individu;
    }

    /**
     * @param Individu $individu
     * @return Individu
     */
    public function update(Individu $individu)  :Individu
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $individu->setHistoModification($date);
            $individu->setHistoModificateur($user);
            $this->getEntityManager()->flush($individu);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Individu");
        }

        return $individu;
    }

    /**
     * @param Individu $individu
     * @return Individu
     */
    public function historise(Individu $individu)  :Individu
    {
        try {
            $individu->historiser();
            $this->getEntityManager()->flush($individu);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Individu");
        }
        return $individu;
    }

    /**
     * @param Individu $individu
     * @return Individu
     */
    public function restore(Individu $individu)  :Individu
    {
        try {
            $individu->dehistoriser();
            $this->getEntityManager()->flush($individu);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Individu");
        }
        return $individu;
    }

    /**
     * @param Individu $individu
     * @return Individu
     */
    public function delete(Individu $individu) : Individu
    {
        try {
            $this->getEntityManager()->remove($individu);
            $this->getEntityManager()->flush($individu);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Individu");
        }

        return $individu;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Individu
     */
    public function getRequestedIndividu(AbstractActionController $controller, string $param='Individu')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Individu $individu */
        $individu = $this->getRepository()->find($id);
        return $individu;
    }

    public function setEntityClass(string $entityClass)
    {
        // TODO: Implement setEntityClass() method.
    }
}