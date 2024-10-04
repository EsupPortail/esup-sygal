<?php

namespace Application\Service\AnneeUniv;

use Application\Entity\AnneeUniv;
use DateInterval;
use DateTime;

class AnneeUnivService
{
    /**
     * Spécification pour calculer la date de bascule d'une année universitaire sur la suivante.
     */
    protected string $specDateBascule; // Ex : '-10 months'; // revient à basculer le 01/11

    /**
     * Spécification de la date de début d'une année universitaire, *fonction de la date de bascule*.
     */
    protected string $specAnneeUnivDateDebut; // Ex : '01/11/%s 00:00:00';

    /**
     * Spécification de la date de fin d'une année universitaire, *fonction de la date de bascule*.
     */
    protected string $specAnneeUnivDateFin; // Ex : '31/10/%s 23:59:59';

    /**
     * @param string $specDateBascule Spécification pour calculer la date de bascule d'une année universitaire sur la suivante.
     * @param string $specAnneeUnivDateDebut Spécification de la date de début d'une année universitaire, *fonction de la date de bascule*.
     * @param string $specAnneeUnivDateFin Spécification de la date de fin d'une année universitaire, *fonction de la date de bascule*.
     */
    public function __construct(string $specDateBascule, string $specAnneeUnivDateDebut, string $specAnneeUnivDateFin)
    {
        $this->specDateBascule = $specDateBascule;
        $this->specAnneeUnivDateDebut = $specAnneeUnivDateDebut;
        $this->specAnneeUnivDateFin = $specAnneeUnivDateFin;
    }

    /**
     * Retourne une instance correspondant à l'année universitaire précédent celle en cours.
     */
    public function precedente(): AnneeUniv
    {
        return $this->fromDate((new DateTime())->sub(new DateInterval('P1Y')));
    }

    /**
     * Retourne une instance correspondant à l'année universitaire suivante celle en cours.
     */
    public function suivante(): AnneeUniv
    {
        return $this->fromDate((new DateTime())->add(new DateInterval('P1Y')));
    }

    /**
     * Retourne une instance correspondant à l'année universitaire courante.
     */
    public function courante(): AnneeUniv
    {
        return $this->fromDate(new DateTime());
    }

    /**
     * Construit une instance correspondant à l'année universitaire de la date est spécifiée,
     * ou à l'année universitaire courante.
     */
    public function fromDate(DateTime $date): AnneeUniv
    {
        $premiereAnnee = $this->computePremiereAnneeFromDate($date);

        return AnneeUniv::fromPremiereAnnee($premiereAnnee);
    }

    /**
     * Calcule la première année à partir de la date spécifiée.
     *
     * Exemples si la date de bascule est le 01/11 :
     *   - 15/01/2022 => Année univ 2021/2022 => première année : 2021
     *   - 09/07/2022 => idem
     *   - 11/09/2022 => idem
     *   - 01/11/2022 => Année univ 2022/2023 => première année : 2022
     *   - 15/01/2023 => idem
     */
    protected function computePremiereAnneeFromDate(DateTime $date): int
    {
        $modifiedDate = clone $date;
        return (int)$modifiedDate->modify($this->specDateBascule)->format('Y');
    }

    public function computeDateDebut(AnneeUniv $anneeUniv): DateTime
    {
        return DateTime::createFromFormat('d/m/Y H:i:s',
            sprintf($this->specAnneeUnivDateDebut, $anneeUniv->getPremiereAnnee()));
    }

    public function computeDateFin(AnneeUniv $anneeUniv): DateTime
    {
        return DateTime::createFromFormat('d/m/Y H:i:s',
            sprintf($this->specAnneeUnivDateFin, $anneeUniv->getPremiereAnnee() + 1));
    }
}