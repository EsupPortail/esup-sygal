<?php

namespace RapportActivite\Entity;

use RapportActivite\Hydrator\FormationHydrator;

class Formation
{
    private ?string $intitule = null;
    private int $temps = 0; // en heures

    public static function fromArray($array): self
    {
        return (new FormationHydrator(false))->hydrate($array, new static());
    }

    public function toArray(): array
    {
        return [
            'intitule' => $this->intitule,
            'temps' => $this->temps,
        ];
    }

    /**
     * @return string|null
     */
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    /**
     * @param string|null $intitule
     * @return self
     */
    public function setIntitule(?string $intitule): self
    {
        $this->intitule = $intitule;
        return $this;
    }

    /**
     * @return int
     */
    public function getTemps(): int
    {
        return $this->temps;
    }

    /**
     * @param int $temps
     * @return self
     */
    public function setTemps(int $temps): self
    {
        $this->temps = $temps;
        return $this;
    }
}