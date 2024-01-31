<?php

namespace Admission\Service\ConventionFormationDoctorale;

use Admission\Entity\Db\ConventionFormationDoctorale;
use Application\Service\BaseService;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;

class ConventionFormationDoctoraleService extends BaseService
{

    public function getRepository()
    {
        return $this->entityManager->getRepository(ConventionFormationDoctorale::class);
    }

    /**
     * @param ConventionFormationDoctorale $conventionFormationDoctorale
     * @return ConventionFormationDoctorale
     */
    public function save(ConventionFormationDoctorale $conventionFormationDoctorale): ConventionFormationDoctorale
    {
        try {
            $this->getEntityManager()->persist($conventionFormationDoctorale);
            $this->getEntityManager()->flush($conventionFormationDoctorale);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'une ConventionFormationDoctorale");
        }

        return $conventionFormationDoctorale;
    }

    /**
     * @param ConventionFormationDoctorale $conventionFormationDoctorale
     * @return ConventionFormationDoctorale
     */
    public function historise(ConventionFormationDoctorale $conventionFormationDoctorale): ConventionFormationDoctorale
    {
        try {
            $conventionFormationDoctorale->historiser();
            $this->getEntityManager()->flush($conventionFormationDoctorale);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'une ConventionFormationDoctorale");
        }
        return $conventionFormationDoctorale;
    }

    /**
     * @param ConventionFormationDoctorale $conventionFormationDoctorale
     * @return ConventionFormationDoctorale
     */
    public function restore(ConventionFormationDoctorale $conventionFormationDoctorale): ConventionFormationDoctorale
    {
        try {
            $conventionFormationDoctorale->dehistoriser();
            $this->getEntityManager()->flush($conventionFormationDoctorale);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'une ConventionFormationDoctorale");
        }
        return $conventionFormationDoctorale;
    }

    /**
     * @param ConventionFormationDoctorale $conventionFormationDoctorale
     * @return void
     * @throws ORMException
     */
    public function delete(ConventionFormationDoctorale $conventionFormationDoctorale): void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $this->getEntityManager()->remove($conventionFormationDoctorale);
            $this->getEntityManager()->flush($conventionFormationDoctorale);

            // commit
            $this->commit();
        } catch (ORMException $e) {
            $this->rollBack();
            throw $e;
        }
    }
}