<?php

namespace Horodatage\Entity\Db;

use DateTime;
use UnicaenAuth\Entity\Db\AbstractUser;

class Horodatage {

    private ?int $id = null;
    private ?DateTime $date = null;
    private ?AbstractUser $utilisateur = null;
    private ?string $type = null;
    private ?string $complement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(?DateTime $date): void
    {
        $this->date = $date;
    }

    public function getUtilisateur(): ?AbstractUser
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?AbstractUser $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): void
    {
        $this->complement = $complement;
    }
}