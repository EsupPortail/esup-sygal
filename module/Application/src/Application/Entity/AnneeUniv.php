<?php

namespace Application\Entity;

use Application\Filter\AnneeUnivFormatter;
use DateInterval;
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
     * @var self[]
     */
    static protected $instances = [];

    /**
     * Constructeur non public.
     */
    protected function __construct()
    {
        $this->formatter = new AnneeUnivFormatter();
    }

    /**
     * Construit une instance correspondant à l'année universitaire dont la 1ere année est spécifiée.
     *
     * @param int $premiereAnnee Ex : 2021 pour l'année universitaire 2021/2022
     * @return \Application\Entity\AnneeUniv
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

    /**
     * Construit une instance correspondant à l'année universitaire de la date est spécifiée,
     * ou à l'année universitaire courante.
     */
    static public function fromDate(DateTime $date): AnneeUniv
    {
        $premiereAnnee = (new AnneeUniv)->computePremiereAnneeFromDate($date);

        return static::fromPremiereAnnee($premiereAnnee);
    }

    /**
     * Retourne une instance correspondant à l'année universitaire courante.
     *
     * @return \Application\Entity\AnneeUniv
     */
    static public function courante(): AnneeUniv
    {
        return static::fromDate(new DateTime());
    }

    /**
     * Retourne une instance correspondant à l'année universitaire précédent celle en cours.
     *
     * @return \Application\Entity\AnneeUniv
     */
    static public function precedente(): AnneeUniv
    {
        return static::fromDate((new DateTime())->sub(new DateInterval('P1Y')));
    }

    /**
     * @param string $separator
     * @return string
     */
    public function toString(string $separator = '/'): string
    {
        return $this->formatter->filter($this->premiereAnnee, $separator);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
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