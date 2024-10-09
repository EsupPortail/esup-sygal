<?php

namespace Admission\Service\Etudiant;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Repository\EtudiantRepository;
use Admission\Entity\Db\Verification;
use Admission\Service\Verification\VerificationServiceAwareTrait;
use Application\Service\BaseService;
use Doctrine\ORM\Exception\ORMException;
use UnicaenApp\Exception\RuntimeException;

class EtudiantService extends BaseService
{
    use VerificationServiceAwareTrait;

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
            $verification = $etudiant->getVerificationEtudiant()->first();
            if($verification instanceof Verification){
                $this->verificationService->delete($verification);
            }

            $this->getEntityManager()->remove($etudiant);
            $this->getEntityManager()->flush($etudiant);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Etudiant");
        }

        return $etudiant;
    }
}