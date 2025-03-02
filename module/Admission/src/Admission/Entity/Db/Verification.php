<?php

namespace Admission\Entity\Db;

use Individu\Entity\Db\Individu;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * Verification
 */
class Verification implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var string|null
     */
    private $commentaire;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Individu
     */
    private $individu;

    private ?Etudiant $etudiant = null;

    /**
     * @var Inscription
     */
    private $inscription;

    /**
     * @var Financement
     */
    private $financement;

    /**
     * @var Document
     */
    private $document;

    private ?bool $estComplet = null;

    /**
     * Set commentaire.
     *
     * @param string|null $commentaire
     *
     * @return Verification
     */
    public function setCommentaire($commentaire = null)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire.
     *
     * @return string|null
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function getEstComplet(): ?bool
    {
        return $this->estComplet;
    }

    public function setEstComplet(?bool $estComplet): void
    {
        $this->estComplet = $estComplet;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set individu.
     *
     * @param Individu|null $individu
     *
     * @return Verification
     */
    public function setIndividu(Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Get individu.
     *
     * @return Individu|null
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * Set etudiant.
     *
     * @param Etudiant|null $etudiant
     *
     * @return Verification
     */
    public function setEtudiant(Etudiant $etudiant = null)
    {
        $this->etudiant = $etudiant;

        return $this;
    }

    /**
     * Get etudiant.
     *
     * @return Etudiant|null
     */
    public function getEtudiant()
    {
        return $this->etudiant;
    }

    /**
     * Set inscription.
     *
     * @param Inscription|null $inscription
     *
     * @return Verification
     */
    public function setInscription(Inscription $inscription = null)
    {
        $this->inscription = $inscription;

        return $this;
    }

    /**
     * Get inscription.
     *
     * @return Inscription|null
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * Set financement.
     *
     * @param Financement|null $financement
     *
     * @return Verification
     */
    public function setFinancement(Financement $financement = null)
    {
        $this->financement = $financement;

        return $this;
    }

    /**
     * Get financement.
     *
     * @return Financement|null
     */
    public function getFinancement()
    {
        return $this->financement;
    }

    /**
     * Set document.
     *
     * @param Document|null $document
     *
     * @return Verification
     */
    public function setDocument(Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return Document|null
     */
    public function getDocument()
    {
        return $this->document;
    }

    public function __toString(): string
    {
        return "Vérification : ".$this->id;
    }
}
