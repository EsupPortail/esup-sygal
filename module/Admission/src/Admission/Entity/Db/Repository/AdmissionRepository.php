<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class AdmissionRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un dossier d'Admission à partir de l'ID de son créateur.
     *
     * @param string $individu
     * @return Admission
     */
    public function findOneByIndividu($individu){
        return $this->findOneBy(['individuId' => $individu]);
    }
}