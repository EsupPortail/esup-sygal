<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Individu;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\UserContextServiceAwareTrait;

class IndividuRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Individu à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Individu
     */
    public function findOneByAdmission($id): Individu
    {
        return $this->findOneBy(['admission' => $id]);
    }

    /**
     * Recherche d'un fieldset Individu à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Individu
     */
    public function findOneByIndividuId($id){
        return $this->findOneBy(['individuId' => $id]);
    }
}