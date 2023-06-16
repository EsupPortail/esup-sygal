<?php

namespace Doctorant\Entity\Db;

use Doctorant\Entity\Db\Interface\HasDoctorantInterface;
use Doctorant\Entity\Db\Trait\HasDoctorantTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class MissionEnseignement implements HasDoctorantInterface, HistoriqueAwareInterface {
    use HasDoctorantTrait;
    use HistoriqueAwareTrait;

    private ?int $id = null;
    private ?int $anneeUniversitaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnneeUniversitaire(): ?int
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?int $anneeUniversitaire): void
    {
        $this->anneeUniversitaire = $anneeUniversitaire;
    }

}