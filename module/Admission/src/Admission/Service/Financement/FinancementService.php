<?php

namespace Admission\Service\Financement;

use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Repository\FinancementRepository;
use Admission\Entity\Db\Verification;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Service\BaseService;
use Doctrine\ORM\Exception\ORMException;
use UnicaenApp\Exception\RuntimeException;

class FinancementService extends BaseService
{
    use VerificationServiceAwareTrait;

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
            $verification = $financement->getVerificationFinancement()->first();
            if($verification instanceof Verification){
                $this->verificationService->delete($verification);
            }

            $this->getEntityManager()->remove($financement);
            $this->getEntityManager()->flush($financement);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Financement");
        }

        return $financement;
    }
}