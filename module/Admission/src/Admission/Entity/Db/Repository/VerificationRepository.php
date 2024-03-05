<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Document;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
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

    /**
     * Recherche d'un fieldset Verification à partir d'un fieldset Inscription.
     *
     * @param Inscription $inscription
     * @return Verification|null
     */
    public function findOneByInscription(Inscription $inscription): Verification|null
    {
        return $this->findOneBy(['inscription' => $inscription]);
    }

    /**
     * Recherche d'un fieldset Verification à partir d'un fieldset Financement.
     *
     * @param Financement $financement
     * @return Verification|null
     */
    public function findOneByFinancement(Financement $financement): Verification|null
    {
        return $this->findOneBy(['financement' => $financement]);
    }

    /**
     * Recherche d'un fieldset Verification à partir d'un fieldset Document.
     *
     * @param Document $document
     * @return Verification|null
     */
    public function findOneByDocument(Document $document): Verification|null
    {
        return $this->findOneBy(['document' => $document]);
    }


}