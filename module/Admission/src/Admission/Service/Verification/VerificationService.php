<?php

namespace Admission\Service\Verification;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Repository\VerificationRepository;
use Admission\Entity\Db\Verification;
use Admission\Entity\Db\Repository\AdmissionValidationRepository;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\ORMException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class VerificationService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @return VerificationRepository
     */
    public function getRepository(): VerificationRepository
    {
        /** @var VerificationRepository $repo */
        $repo = $this->entityManager->getRepository(Verification::class);

        return $repo;
    }

    /**
     * @param Verification $verification
     * @return Verification
     */
    public function create(Verification $verification) : Verification
    {
        try {
            $individu = $this->userContextService->getIdentityIndividu();
            $verification->setIndividu($individu);
            $this->getEntityManager()->persist($verification);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'une Vérification");
        }

        return $verification;
    }



    /**
     * @param Verification $verification
     * @return Verification
     */
    public function update(Verification $verification)  :Verification
    {
        try {
            $individu = $this->userContextService->getIdentityIndividu();
            $verification->setIndividu($individu);
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Verification");
        }

        return $verification;
    }

    /**
     * @param Verification $verification
     * @return Verification
     */
    public function historise(Verification $verification)  :Verification
    {
        try {
            $verification->historiser();
            $this->getEntityManager()->flush($verification);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Verification");
        }
        return $verification;
    }

    /**
     * @param Verification $verification
     * @return Verification
     */
    public function restore(Verification $verification)  :Verification
    {
        try {
            $verification->dehistoriser();
            $this->getEntityManager()->flush($verification);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Verification");
        }
        return $verification;
    }

    /**
     * @param Verification $verification
     * @return Verification
     */
    public function delete(Verification $verification) : Verification
    {
        try {
            $this->getEntityManager()->remove($verification);
            $this->getEntityManager()->flush($verification);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Verification");
        }

        return $verification;
    }
}