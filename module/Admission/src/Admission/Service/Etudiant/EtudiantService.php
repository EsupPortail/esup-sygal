<?php

namespace Admission\Service\Etudiant;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Repository\EtudiantRepository;
use Admission\Entity\Db\Repository\ValidationRepository;
use Admission\Entity\Db\Verification;
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

class EtudiantService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return EtudiantRepository
     */
    public function getRepository(): EtudiantRepository
    {
        /** @var EtudiantRepository $repo */
        $repo = $this->entityManager->getRepository(Etudiant::class);

        return $repo;
    }

    /**
     * @param Etudiant $etudiant
     * @param Admission $admission
     * @return Etudiant
     */
    public function create(Etudiant $etudiant, Admission $admission) : Etudiant
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $etudiant->setHistoModification($date);
            $etudiant->setHistoModificateur($user);
            $this->getEntityManager()->persist($admission);
            $this->getEntityManager()->persist($etudiant);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Etudiant");
        }

        return $etudiant;
    }

    /**
     * @param Etudiant $etudiant
     * @return Etudiant
     */
    public function update(Etudiant $etudiant)  :Etudiant
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $etudiant->setHistoModification($date);
            $etudiant->setHistoModificateur($user);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Etudiant");
        }

        return $etudiant;
    }

    /**
     * @param Etudiant $etudiant
     * @return Etudiant
     */
    public function historise(Etudiant $etudiant)  :Etudiant
    {
        try {
            $etudiant->historiser();
            $this->getEntityManager()->flush($etudiant);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Etudiant");
        }
        return $etudiant;
    }

    /**
     * @param Etudiant $etudiant
     * @return Etudiant
     */
    public function restore(Etudiant $etudiant)  :Etudiant
    {
        try {
            $etudiant->dehistoriser();
            $this->getEntityManager()->flush($etudiant);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Etudiant");
        }
        return $etudiant;
    }

    /**
     * @param Etudiant $etudiant
     * @return Etudiant
     */
    public function delete(Etudiant $etudiant) : Etudiant
    {
        try {
            $this->getEntityManager()->remove($etudiant);
            $this->getEntityManager()->flush($etudiant);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Etudiant");
        }

        return $etudiant;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Etudiant
     */
    public function getRequestedEtudiant(AbstractActionController $controller, string $param='Etudiant')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Etudiant $etudiant */
        $etudiant = $this->getRepository()->find($id);
        return $etudiant;
    }

    public function setEntityClass(string $entityClass)
    {
        // TODO: Implement setEntityClass() method.
    }
}