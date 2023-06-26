<?php

namespace Doctorant\Service\MissionEnseignement;

use Doctorant\Entity\Db\MissionEnseignement;
use Doctorant\Entity\Db\Repository\MissionEnseignementRepository;
use Doctrine\ORM\Exception\ORMException;
use RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MissionEnseignementService {
    use EntityManagerAwareTrait;

    public function getRepository(): MissionEnseignementRepository
    {
        /** @var MissionEnseignementRepository $repo */
        $repo = $this->entityManager->getRepository(MissionEnseignement::class);

        return $repo;
    }

    /** ENTITY MANAGEMENT *********************************************************************************************/

    public function create(MissionEnseignement $mission) : MissionEnseignement
    {
        try {
            $this->getEntityManager()->persist($mission);
            $this->getEntityManager()->flush($mission);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $mission;
    }

    public function delete(MissionEnseignement $mission) : MissionEnseignement
    {
        try {
            $this->getEntityManager()->remove($mission);
            $this->getEntityManager()->flush($mission);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base de donnée",0,$e);
        }
        return $mission;
    }
}