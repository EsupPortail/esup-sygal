<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Verification;
use Application\Entity\Db\Repository\DefaultEntityRepository;

class VerificationRepository extends DefaultEntityRepository{
    /**
     * Recherche d'un fieldset Verification à partir d'un fieldset Etudiant.
     *
     * @param Etudiant $etudiant
     * @return Verification|null
     */
    public function findOneByEtudiant(Etudiant $etudiant): Verification|null
    {
        return $this->findOneBy(['etudiant' => $etudiant]);
    }
}