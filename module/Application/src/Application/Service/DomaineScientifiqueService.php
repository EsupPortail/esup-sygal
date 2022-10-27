<?php

namespace Application\Service;

use Application\Entity\Db\DomaineScientifique;
use Application\Entity\Db\Repository\DomaineScientifiqueRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;

class DomaineScientifiqueService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getEntityManager()->getRepository(DomaineScientifique::class);
        return $repo;
    }

    /**
     * @param string $libelle
     * @return DomaineScientifique
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createDomaineScientifique($libelle): DomaineScientifique
    {
        /** @var DomaineScientifiqueRepository $repo */
        $repo = $this->getRepository();
        $result = $repo->findOneBy(["libelle" => $libelle]);
        if ($result !== null) {
            throw new RuntimeException("Il existe déjà un domaine scientifique libellé '".$libelle."'");
        }
        $domaine = new DomaineScientifique();
        $domaine->setLibelle($libelle);
        try {
            $this->getEntityManager()->persist($domaine);
            $this->getEntityManager()->flush($domaine);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du domaine scientifique");
        }

        return $domaine;
    }

    /**
     * @param DomaineScientifique $domaine
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDomaineScientifique($domaine)
    {
        $this->getEntityManager()->flush($domaine);
    }

}