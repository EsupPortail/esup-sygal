<?php

namespace Application\Service\Pays;

use Application\Entity\Db\Pays;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\BaseService;

/**
 * @method Pays|null findOneBy(array $criteria, array $orderBy = null)
 */
class PaysService extends BaseService
{
    /**
     * @return DefaultEntityRepository
     */
    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(Pays::class);

        return $repo;
    }

    public function getNationalitesAsOptions(): array
    {
        $qb = $this->getRepository()->createQueryBuilder('p')
            ->where('p.libelleNationalite is not null')
            ->orderBy('p.libelleNationalite');

        /** @var Pays[] $pays */
        $pays = $qb->getQuery()->getResult();
        $options = [];
        foreach ($pays as $p) {
            $label = $p->getLibelleNationalite();
            if(!in_array($label, $options)){
                $options[$p->getId()] = $label;
            }
        }
        return $options;
    }

    public function getPaysAsOptions(): array
    {
        $qb = $this->getRepository()->createQueryBuilder('p')
            ->orderBy('p.libelle');

        /** @var Pays[] $pays */
        $pays = $qb->getQuery()->getResult();
        $options = [];
        foreach ($pays as $p) {
            $label = $p->getLibelle();
            if(!in_array($label, $options)){
                $options[$p->getId()] = $label;
            }
        }
        return $options;
    }
}