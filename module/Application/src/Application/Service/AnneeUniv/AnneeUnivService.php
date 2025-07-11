<?php

namespace Application\Service\AnneeUniv;

use Application\Constants;
use Application\Entity\AnneeUniv;
use DateInterval;
use DateTime;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

class AnneeUnivService
{
    public const function__compute_premiere_annee_from_date = 'annee_univ_compute_premiere_annee_from_date';
    public const function__compute_date_debut = 'annee_univ_compute_date_debut';
    public const function__compute_date_fin = 'annee_univ_compute_date_fin';

    protected Connection $connection;

    /**
     * Spécification pour calculer la date de bascule d'une année universitaire sur la suivante.
     */
    protected string $specDateBascule; // Ex : '-10 months'; // revient à basculer le 01/11

    /**
     * Spécification de la date de début d'une année universitaire, *en cohérence avec la date de bascule*.
     */
    protected string $specAnneeUnivDateDebut; // Ex : '01/11/%s 00:00:00';

    /**
     * Spécification de la date de fin d'une année universitaire, *en cohérence avec la date de bascule*.
     */
    protected string $specAnneeUnivDateFin; // Ex : '31/10/%s 23:59:59';

    /**
     * @param string $specDateBascule Spécification pour calculer la date de bascule d'une année universitaire sur la suivante.
     * @param string $specAnneeUnivDateDebut Spécification de la date de début d'une année universitaire, *en cohérence avec la date de bascule*.
     * @param string $specAnneeUnivDateFin Spécification de la date de fin d'une année universitaire, *en cohérence avec la date de bascule*.
     */
    public function __construct(string $specDateBascule, string $specAnneeUnivDateDebut, string $specAnneeUnivDateFin)
    {
        $this->specDateBascule = $specDateBascule;
        $this->specAnneeUnivDateDebut = $specAnneeUnivDateDebut;
        $this->specAnneeUnivDateFin = $specAnneeUnivDateFin;
    }

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
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
     *
     * @throws \Doctrine\DBAL\Exception
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
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function computePremiereAnneeFromDate(DateTime $date): int
    {
//        $modifiedDate = clone $date;
//        return (int)$modifiedDate->modify($this->specDateBascule)->format('Y');
        $params = ['date' => $date->format(Constants::DATETIME_FORMAT_DB)];

        $result = $this->connection->executeQuery(
            $sql = 'select annee_univ_compute_premiere_annee_from_date(:date)',
            $params
        );

        $premiereAnnee = $result->fetchNumeric()[0] ?? null;
        Assert::notNull($premiereAnnee, "La fonction SQL suivante a retourné une valeur inattendue : " . $sql);

        return $premiereAnnee;
    }

    /**
     * Calcule la date de début de l'année universitaire spécifiée,
     * en fonction de la Spécification de la date de début d'une année universitaire.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function computeDateDebut(AnneeUniv $anneeUniv): DateTime
    {
//        return DateTime::createFromFormat('d/m/Y H:i:s',
//            sprintf($this->specAnneeUnivDateDebut, $anneeUniv->getPremiereAnnee()));
        $params = ['premiereAnnee' => $anneeUniv->getPremiereAnnee()];

        $result = $this->connection->executeQuery(
            $sql = 'select annee_univ_compute_date_debut(:premiereAnnee)',
            $params
        );

        $timestamptz = current($result->fetchAssociative()) ?? null;
        Assert::notNull($timestamptz, "La fonction SQL suivante a retourné une valeur inattendue : " . $sql);

        return date_create($timestamptz);
    }

    /**
     * Calcule la date de fin de l'année universitaire spécifiée,
     * en fonction de la Spécification de la date de fin d'une année universitaire.
     * @throws \Doctrine\DBAL\Exception
     */
    public function computeDateFin(AnneeUniv $anneeUniv): DateTime
    {
//        return DateTime::createFromFormat('d/m/Y H:i:s',
//            sprintf($this->specAnneeUnivDateFin, $anneeUniv->getPremiereAnnee() + 1));
        $params = ['premiereAnnee' => $anneeUniv->getPremiereAnnee() + 1];

        $result = $this->connection->executeQuery(
            $sql = 'select annee_univ_compute_date_fin(:premiereAnnee)',
            $params
        );

        $timestamptz = current($result->fetchAssociative()) ?? null;
        Assert::notNull($timestamptz, "La fonction SQL suivante a retourné une valeur inattendue : " . $sql);

        return date_create($timestamptz);
    }
}