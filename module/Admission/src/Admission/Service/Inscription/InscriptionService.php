<?php

namespace Admission\Service\Inscription;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Repository\InscriptionRepository;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class InscriptionService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return InscriptionRepository
     */
    public function getRepository(): InscriptionRepository
    {
        /** @var InscriptionRepository $repo */
        $repo = $this->entityManager->getRepository(Inscription::class);

        return $repo;
    }

    /**
     * @param Inscription $inscription
     * @return Inscription
     */
    public function create(Inscription $inscription, Admission $admission) : Inscription
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $inscription->setHistoModification($date);
            $inscription->setHistoModificateur($user);
            $this->getEntityManager()->persist($admission);
            $this->getEntityManager()->persist($inscription);
            $this->getEntityManager()->flush($inscription);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Inscription");
        }

        return $inscription;
    }



    /**
     * @param Inscription $inscription
     * @return Inscription
     */
    public function update(Inscription $inscription)  :Inscription
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $inscription->setHistoModification($date);
            $inscription->setHistoModificateur($user);
            $this->getEntityManager()->flush($inscription);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Inscription");
        }

        return $inscription;
    }

    /**
     * @param Inscription $inscription
     * @return Inscription
     */
    public function historise(Inscription $inscription)  :Inscription
    {
        try {
            $inscription->historiser();
            $this->getEntityManager()->flush($inscription);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Inscription");
        }
        return $inscription;
    }

    /**
     * @param Inscription $inscription
     * @return Inscription
     */
    public function restore(Inscription $inscription)  :Inscription
    {
        try {
            $inscription->dehistoriser();
            $this->getEntityManager()->flush($inscription);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Inscription");
        }
        return $inscription;
    }

    /**
     * @param Inscription $inscription
     * @return Inscription
     */
    public function delete(Inscription $inscription) : Inscription
    {
        try {
            $this->getEntityManager()->remove($inscription);
            $this->getEntityManager()->flush($inscription);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Inscription");
        }

        return $inscription;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Inscription
     */
    public function getRequestedInscription(AbstractActionController $controller, string $param='Inscription')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Inscription $inscription */
        $inscription = $this->getRepository()->find($id);
        return $inscription;
    }
}