<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Inscription;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class InscriptionRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Individu à partir de l'ID de son créateur.
     *
     * @param Admission $admission
     * @return Inscription|null
     */
    public function findOneByAdmission(Admission $admission): Inscription|null
    {
        return $this->findOneBy(['admission' => $admission]);
    }
}