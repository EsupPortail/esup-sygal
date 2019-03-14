<?php

namespace Application\Service\Acteur;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\ActeurRepository;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Service\BaseService;

class ActeurService extends BaseService
{
    /**
     * @return ActeurRepository
     */
    public function getRepository()
    {
        /** @var ActeurRepository $repo */
        $repo = $this->entityManager->getRepository(Acteur::class);

        return $repo;
    }

    /**
     * @param Individu $individu
     * @return Acteur[]
     */
    public function getRapporteurDansTheseEnCours($individu)
    {
        $qb = $this->getEntityManager()->getRepository(Acteur::class)->createQueryBuilder('acteur')
            ->addSelect('these')->join('acteur.these', 'these')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('1 = pasHistorise(acteur)')
            ->andWhere('these.etatThese = :encours')
            ->andWhere('acteur.individu = :individu')
            ->andWhere('role.code = :rapporteurJury OR role.code = :rapprteurAbsent')
            ->setParameter('encours', These::ETAT_EN_COURS)
            ->setParameter('individu', $individu)
            ->setParameter('rapporteurJury', Role::CODE_RAPPORTEUR_JURY)
            ->setParameter('rapprteurAbsent', Role::CODE_RAPPORTEUR_ABSENT)
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}