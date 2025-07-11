<?php

namespace Application\Entity;

use Application\Filter\AnneeUnivFormatter;
use DateTime;
use UnicaenSearch\Filter\SearchFilterValueInterface;
use Webmozart\Assert\Assert;

/**
 * Entité représentant une "année universitaire".
 *
 * Voir aussi le service {@see \Application\Service\AnneeUniv\AnneeUnivService} pour ce qui concerne
 * la date de bascule d'une année universitaire sur la suivante.
 */
class AnneeUniv implements AnneeUnivInterface, SearchFilterValueInterface
{
    protected int $premiereAnnee;
    protected AnneeUnivFormatter $formatter;

    protected DateTime $dateDebut;
    protected DateTime $dateFin;

    /**
     * @var \Application\Entity\AnneeUniv[]
     */
    protected static array $instances = [];

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
    public static function fromPremiereAnnee(int $premiereAnnee): AnneeUniv
    {
        if (array_key_exists($premiereAnnee, static::$instances)) {
            return static::$instances[$premiereAnnee];
        }

        $inst = new static();
        $inst->setPremiereAnnee($premiereAnnee);

        return static::$instances[$premiereAnnee] = $inst;
    }

    /**
     * Version de {@see self::fromPremiereAnnee()} pour plusieurs années.
     */
    public static function fromPremieresAnnees(array $premieresAnnees): array
    {
        Assert::allInteger($premieresAnnees);

        return array_map(fn($annee) => static::fromPremiereAnnee($annee), array_unique($premieresAnnees));
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

    /**
     * Renseigne la date de début de cette année universitaire, ex : 01/09/2024 00:00:00.
     *
     * **NB : il n'y a aucun contrôle/calcul de la date dans cette classe.**
     */
    public function setDateDebut(DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    /**
     * Retourne la date de début de cette année universitaire, ex : 01/09/2024 00:00:00.
     *
     *  **NB : il n'y a aucun contrôle/calcul de la date dans cette classe.**
     */
    public function getDateDebut(): DateTime
    {
        return $this->dateDebut;
    }

    /**
     * Renseigne la date de fin de cette année universitaire, ex : 31/08/2025 23:59:59.
     *
     *  **NB : il n'y a aucun contrôle/calcul de la date dans cette classe.**
     */
    public function setDateFin(DateTime $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * Retourne la date de fin de cette année universitaire, ex : 31/08/2025 23:59:59.
     *
     *  **NB : il n'y a aucun contrôle/calcul de la date dans cette classe.**
     */
    public function getDateFin(): DateTime
    {
        return $this->dateFin;
    }

    public function createSearchFilterValueOption(): array
    {
        return ['value' => (string) $this->getPremiereAnnee(), 'label' => (string) $this];
    }
}