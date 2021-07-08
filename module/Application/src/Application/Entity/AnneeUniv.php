<?php

namespace Application\Entity;

use Application\Filter\AnneeUnivFormatter;
use DateTime;

class AnneeUniv
{
    /**
     * Spécification pour calculer la date de bascule d'une année universitaire sur la suivante.
     */
    const SPEC_DATE_BASCULE = '-10 months'; // revient à basculer le 01/11

    /**
     * @var int
     */
    protected $premiereAnnee;

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
     * Construit une instance correspondant à l'année universitaire de la date est spécifiée,
     * ou à l'année universitaire courante.
     */
    public function __construct(DateTime $date = null)
    {
        $this->formatter = new AnneeUnivFormatter();

        $this->setPremiereAnnee($this->computePremiereAnneeFromDate($date ?: new DateTime()));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->formatter->filter($this->premiereAnnee);
    }

    /**
     * Calcule la première année à partir de la date spécifiée.
     *
     * Exemples :
     * 09/07/2020 => 2019 ;
     * 11/09/2020 => 2020 ;
     * 15/08/2020 => 2020
     *
     * @param \DateTime $date
     * @return int
     */
    protected function computePremiereAnneeFromDate(DateTime $date): int
    {
        return (int) $date->modify(static::SPEC_DATE_BASCULE)->format('Y');
    }

    protected function computeDatesDebFin()
    {
        $dateDeb = DateTime::createFromFormat('d/m/Y H:i:s', sprintf("01/09/%s 00:00:00", $this->premiereAnnee));
        $dateFin = DateTime::createFromFormat('d/m/Y H:i:s', sprintf("31/08/%s 00:00:00", $this->premiereAnnee + 1));
        $this->dateDeb = $dateDeb;
        $this->dateFin = $dateFin;
    }

    protected function setPremiereAnnee(int $premiereAnnee)
    {
        $this->premiereAnnee = $premiereAnnee;
        $this->computeDatesDebFin();
    }

    /**
     * @return int
     */
    public function getPremiereAnnee(): int
    {
        return $this->premiereAnnee;
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