<?php
namespace Formation\Entity\Db;

use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class JustificatifSeance implements HistoriqueAwareInterface {

    use HistoriqueAwareTrait;

    private Justificatif $justificatif;
    private Seance $seance;

    public function getJustificatif(): ?Justificatif
    {
        return $this->justificatif;
    }

    public function setJustificatif(?Justificatif $justificatif): self
    {
        $this->justificatif = $justificatif;

        return $this;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): self
    {
        $this->seance = $seance;

        return $this;
    }
}
