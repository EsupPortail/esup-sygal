<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Individu\Entity\Db\Individu;

class EtudiantRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Etudiant à partir de son dossier d'admission.
     *
     * @param Admission $admission
     * @return Etudiant
     */
    public function findOneByAdmission($admission): Etudiant
    {
        return $this->findOneBy(['admission' => $admission]);
    }

    /**
     * Recherche d'un fieldset Etudiant à partir de son créateur.
     *
     * @param Individu $individu
     * @return Etudiant
     */
    public function findOneByIndividu(Individu $individu){
        return $this->findOneBy(['individu' => $individu]);
    }
}