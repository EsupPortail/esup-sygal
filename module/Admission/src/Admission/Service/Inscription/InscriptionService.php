<?php

namespace Admission\Service\Inscription;

use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Repository\InscriptionRepository;
use Admission\Entity\Db\Verification;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Service\BaseService;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;

class InscriptionService extends BaseService
{
    use VerificationServiceAwareTrait;

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
    public function create(Inscription $inscription) : Inscription
    {
        try {
            $this->getEntityManager()->persist($inscription);
            $this->getEntityManager()->flush();
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
            $verification = $inscription->getVerificationInscription()->first();
            if($verification instanceof Verification){
                $this->verificationService->delete($verification);
            }

            $this->getEntityManager()->remove($inscription);
            $this->getEntityManager()->flush($inscription);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Inscription");
        }

        return $inscription;
    }
}