<?php

namespace Formation\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Justificatif implements HistoriqueAwareInterface, ResourceInterface {
    use HistoriqueAwareTrait;

    private int $id;
    private Fichier $fichier;
    private ?Inscription $inscription = null;
    private Collection $justificatifSeances;

    public function __construct()
    {
        $this->justificatifSeances = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Inscription|null
     */
    public function getInscription(): ?Inscription
    {
        return $this->inscription;
    }

    /**
     * @param Inscription|null $inscription
     */
    public function setInscription(?Inscription $inscription): void
    {
        $this->inscription = $inscription;
    }

    /**
     * @return Fichier
     */
    public function getFichier(): Fichier
    {
        return $this->fichier;
    }

    /**
     * @param Fichier $fichier
     */
    public function setFichier(Fichier $fichier): void
    {
        $this->fichier = $fichier;
    }

    public function getJustificatifSeances()
    {
        return $this->justificatifSeances;
    }

    public function addJustificatifSeance(JustificatifSeance $justificatifSeance): self
    {
        if (!$this->justificatifSeances->contains($justificatifSeance)) {
            $this->justificatifSeances[] = $justificatifSeance;
            $justificatifSeance->setJustificatif($this);
        }

        return $this;
    }

    public function removeJustificatifSeance(JustificatifSeance $justificatifSeance): self
    {
        if ($this->justificatifSeances->contains($justificatifSeance)) {
            $this->justificatifSeances->removeElement($justificatifSeance);
        }

        return $this;
    }

    public function getResourceId()
    {
        return 'Justificatif';
    }
}