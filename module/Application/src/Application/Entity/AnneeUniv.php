<?php

namespace Application\Entity;

use Application\Filter\AnneeUnivFormatter;

/**
 * Entité représentant une "année universitaire".
 *
 * Voir aussi le service {@see \Application\Service\AnneeUniv\AnneeUnivService} pour ce qui concerne
 * la date de bascule d'une année universitaire sur la suivante.
 */
class AnneeUniv implements AnneeUnivInterface
{
    protected int $premiereAnnee;
    protected AnneeUnivFormatter $formatter;

    /**
     * @var \Application\Entity\AnneeUniv[]
     */
    static protected array $instances = [];

    /**
     * Constructeur non public.
     */
    protected function __construct()
    {
        $this->formatter = new AnneeUnivFormatter();
    }

    /**
     * Construit une instance correspondant à l'année universitaire dont la 1ere année est spécifiée.
     * Ex : si la première année est 2021, l'année universitaire est "2021/2022".
     */
    static public function fromPremiereAnnee(int $premiereAnnee): AnneeUniv
    {
        if (array_key_exists($premiereAnnee, static::$instances)) {
            return static::$instances[$premiereAnnee];
        }

        $inst = new static();
        $inst->setPremiereAnnee($premiereAnnee);

        return static::$instances[$premiereAnnee] = $inst;
    }

    public function toString(string $separator = '/'): string
    {
        return $this->formatter->filter($this->premiereAnnee, $separator);
    }

    public function getAnneeUnivToString(): string
    {
        return $this->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    protected function setPremiereAnnee(int $premiereAnnee): void
    {
        $this->premiereAnnee = $premiereAnnee;
    }

    public function getPremiereAnnee(): int
    {
        return $this->premiereAnnee;
    }
}