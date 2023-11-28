<?php

namespace Admission\Service\Financement;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Repository\FinancementRepository;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class FinancementService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return FinancementRepository
     */
    public function getRepository(): FinancementRepository
    {
        /** @var FinancementRepository $repo */
        $repo = $this->entityManager->getRepository(Financement::class);

        return $repo;
    }

    /**
     * @param Financement $financement
     * @return Financement
     */
    public function create(Financement $financement) : Financement
    {
        try {
            $this->getEntityManager()->persist($financement);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Financement");
        }

        return $financement;
    }



    /**
     * @param Financement $financement
     * @return Financement
     */
    public function update(Financement $financement)  :Financement
    {
        try {
            $this->getEntityManager()->flush($financement);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Financement");
        }

        return $financement;
    }

    /**
     * @param Financement $financement
     * @return Financement
     */
    public function historise(Financement $financement)  :Financement
    {
        try {
            $financement->historiser();
            $this->getEntityManager()->flush($financement);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Financement");
        }
        return $financement;
    }

    /**
     * @param Financement $financement
     * @return Financement
     */
    public function restore(Financement $financement)  :Financement
    {
        try {
            $financement->dehistoriser();
            $this->getEntityManager()->flush($financement);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Financement");
        }
        return $financement;
    }

    /**
     * @param Financement $financement
     * @return Financement
     */
    public function delete(Financement $financement) : Financement
    {
        try {
            $this->getEntityManager()->remove($financement);
            $this->getEntityManager()->flush($financement);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Financement");
        }

        return $financement;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Financement
     */
    public function getRequestedFinancement(AbstractActionController $controller, string $param='Financement')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Financement $financement */
        $financement = $this->getRepository()->find($id);
        return $financement;
    }
}