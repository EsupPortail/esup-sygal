<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Financement;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class FinancementRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Financement à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Financement
     */
    public function findOneByAdmission($id): Financement|null
    {
        return $this->findOneBy(['admission' => $id]);
    }
}