<?php

namespace Formation\Service\JustificatifSeance;

use Doctrine\ORM\Exception\ORMException;
use Formation\Entity\Db\JustificatifSeance;
use Formation\Entity\Db\Repository\JustificatifSeanceRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class JustificatifSeanceService
{
    use EntityManagerAwareTrait;

    /**
     * @return JustificatifSeanceRepository
     */
    public function getRepository(): JustificatifSeanceRepository
    {
        /** @var JustificatifSeanceRepository $repo */
        $repo = $this->entityManager->getRepository(JustificatifSeance::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param JustificatifSeance $justificatifSeance
     * @return JustificatifSeance
     */
    public function update(JustificatifSeance $justificatifSeance): JustificatifSeance
    {
        try {
            $this->getEntityManager()->flush($justificatifSeance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [JustificatifSeance]", 0, $e);
        }
        return $justificatifSeance;
    }

    /**
     * @param JustificatifSeance $justificatifSeance
     * @return JustificatifSeance
     */
    public function historise(JustificatifSeance $justificatifSeance): JustificatifSeance
    {
        try {
            $justificatifSeance->historiser();
            $this->getEntityManager()->flush($justificatifSeance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [JustificatifSeance]", 0, $e);
        }
        return $justificatifSeance;
    }

    /**
     * @param JustificatifSeance $justificatifSeance
     * @return JustificatifSeance
     */
    public function restore(JustificatifSeance $justificatifSeance): JustificatifSeance
    {
        try {
            $justificatifSeance->dehistoriser();
            $this->getEntityManager()->flush($justificatifSeance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [JustificatifSeance]", 0, $e);
        }
        return $justificatifSeance;
    }

    /**
     * @param JustificatifSeance $justificatifSeance
     * @return JustificatifSeance
     */
    public function delete(JustificatifSeance $justificatifSeance): JustificatifSeance
    {
        try {
            $this->getEntityManager()->remove($justificatifSeance);
            $this->getEntityManager()->flush($justificatifSeance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenue en base pour une entité [JustificatifSeance]", 0, $e);
        }
        return $justificatifSeance;
    }
}