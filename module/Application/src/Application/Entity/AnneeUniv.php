<?php

namespace Application\Entity;

use Application\Filter\AnneeUnivFormatter;
use DateTime;

class AnneeUniv
{
    /**
     * @var int
     */
    protected $annee;

    /**
     * @var DateTime
     */
    protected $dateDeb;

    /**
     * @var DateTime
     */
    protected $dateFin;

    /**
     * @var \Application\Filter\AnneeUnivFormatter
     */
    protected $formatter;

    /**
     * AnneeUniv constructor.
     */
    protected function __construct()
    {
        $this->formatter = new AnneeUnivFormatter();
    }

    /**
     * @return self
     */
    static public function courante(): self
    {
        return static::fromDate(new DateTime());
    }

    /**
     * @param DateTime $date
     * @return self
     */
    static public function fromDate(DateTime $date): self
    {
        $annee = (int) $date->modify('-6 months')->format('Y');

        $instance = new static();
        $instance->annee = $annee;
        $instance->computeDatesDebFin($annee);

        return $instance;
    }

    protected function computeDatesDebFin(int $annee)
    {
        $dateDeb = DateTime::createFromFormat('d/m/Y H:i:s', sprintf("01/09/%s 00:00:00", $annee));
        $dateFin = DateTime::createFromFormat('d/m/Y H:i:s', sprintf("31/08/%s 00:00:00", $annee+1));
        $this->dateDeb = $dateDeb;
        $this->dateFin = $dateFin;
    }
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->formatter->filter($this->annee);
    }

    /**
     * @return int
     */
    public function getAnnee(): int
    {
        return $this->annee;
    }

    /**
     * @return DateTime
     */
    public function getDateDeb(): DateTime
    {
        return $this->dateDeb;
    }

    /**
     * @return DateTime
     */
    public function getDateFin(): DateTime
    {
        return $this->dateFin;
    }
}