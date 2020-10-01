<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\DomaineScientifique;

class DomaineScientifiqueRepository extends DefaultEntityRepository
{

//    /**
//     * @param int $id
//     * @return DomaineScientifique
//     */
//    public function find($id) {
//
//        /** @var DomaineScientifique $domaine */
//        $domaine = $this->findOneBy(["id" => $id]);
//        return $domaine;
//    }

    /**
     * @return DomaineScientifique[]
     */
    public function findAll() {
        $domaines = $this->findAll();
        return $domaines;
    }

    /**
     * @param string $libelle
     * @return DomaineScientifique
     */
    public function findByLibelle($libelle)
    {
        /** @var DomaineScientifique $domaine */
        $domaine = $this->findOneBy(["libelle" => $libelle]);
        return $domaine;
    }

}