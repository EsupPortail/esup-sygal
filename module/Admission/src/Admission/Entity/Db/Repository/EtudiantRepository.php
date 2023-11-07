<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Individu;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\UserContextServiceAwareTrait;

class EtudiantRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Etudiant à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Etudiant
     */
    public function findOneByAdmission($id): Etudiant
    {
        return $this->findOneBy(['admission' => $id]);
    }

    /**
     * Recherche d'un fieldset Etudiant à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Etudiant
     */
    public function findOneByIndividu($id){
        return $this->findOneBy(['individu' => $id]);
    }
}