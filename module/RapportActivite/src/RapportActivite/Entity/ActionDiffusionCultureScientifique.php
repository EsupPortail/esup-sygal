<?php

namespace RapportActivite\Entity;

use DateTime;
use RapportActivite\Hydrator\ActionDiffusionCultureScientifiqueHydrator;

class ActionDiffusionCultureScientifique
{
    private ?string $nature = null;
    private ?string $public = null;
    private int $temps = 0; // en heures
    private ?DateTime $date = null;

    public static function fromArray($array): self
    {
        return (new ActionDiffusionCultureScientifiqueHydrator(false))->hydrate($array, new static());
    }

    public function toArray(): array
    {
        return [
            'nature' => $this->nature,
            'public' => $this->public,
            'temps' => $this->temps,
            'date' => $this->date->format('Y-m-d'),
        ];
    }

    /**
     * @return string|null
     */
    public function getNature(): ?string
    {
        return $this->nature;
    }

    /**
     * @param string|null $nature
     * @return self
     */
    public function setNature(?string $nature): self
    {
        $this->nature = $nature;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPublic(): ?string
    {
        return $this->public;
    }

    /**
     * @param string|null $public
     * @return self
     */
    public function setPublic(?string $public): self
    {
        $this->public = $public;
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

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     * @return self
     */
    public function setDate(?DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }
}