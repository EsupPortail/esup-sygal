<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Inscription;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class InscriptionRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Individu à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Inscription
     */
    public function findOneByAdmission($id): Inscription|null
    {
        var_dump($id);
        return $this->findOneBy(['admission' => $id]);
    }
}