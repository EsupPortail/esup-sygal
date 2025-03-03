<?php

namespace Application\Entity\Db;

use Structure\Entity\Db\Etablissement;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

class VersionDiplome
{
    use SourceAwareTrait;
    use HistoriqueAwareTrait;

    private int $id;
    private string $code;
    private string $libelleCourt;
    private string $libelleLong;
    private bool $theseCompatible = false;
    private bool $hdrCompatible = false;

    private Etablissement $etablissement;

    public function __toString(): string
    {
        return $this->getLibelleLong();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLibelleCourt(): string
    {
        return $this->libelleCourt;
    }

    public function getLibelleLong(): string
    {
        return $this->libelleLong;
    }

    public function isTheseCompatible(): bool
    {
        return $this->theseCompatible;
    }

    public function isHdrCompatible(): bool
    {
        return $this->hdrCompatible;
    }

    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }
}