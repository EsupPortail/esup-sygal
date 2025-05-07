<?php

namespace Application\Service\Pays;

use Application\Entity\Db\Pays;
use Application\Entity\Db\Repository\PaysRepository;
use Application\Service\BaseService;

class PaysService extends BaseService
{
    public function getRepository(): PaysRepository
    {
        /** @var PaysRepository $repo */
        $repo = $this->entityManager->getRepository(Pays::class);

        return $repo;
    }

    /**
     * @deprecated A déplacer dans PaysRepository !
     */
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

    /**
     * @deprecated A déplacer dans PaysRepository !
     */
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