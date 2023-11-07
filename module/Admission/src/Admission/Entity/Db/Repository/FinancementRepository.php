<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Financement;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class FinancementRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Financement à partir de l'ID de son créateur.
     *
     * @param Admission $admission
     * @return Financement|null
     */
    public function findOneByAdmission(Admission $admission): Financement|null
    {
        return $this->findOneBy(['admission' => $admission]);
    }
}