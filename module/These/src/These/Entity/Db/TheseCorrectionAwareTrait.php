<?php

namespace These\Entity\Db;

use Application\Constants;
use Assert\Assertion;
use BadMethodCallException;
use DateInterval;
use DateTime;
use UnicaenApp\Exception\RuntimeException;

trait TheseCorrectionAwareTrait
{
    public static string $CORRECTION_AUTORISEE_OBLIGATOIRE = 'obligatoire';
    public static string $CORRECTION_AUTORISEE_FACULTATIVE = 'facultative';

    public static array $correctionsLibelles = [
        'obligatoire' => "Obligatoire",
        'facultative' => "Facultative",
    ];
    public static array $correctionsLibellesPluriels = [
        'obligatoire' => "Obligatoires",
        'facultative' => "Facultatives",
    ];

    public static string $CORRECTION_OBLIGATOIRE_INTERVAL = 'P3M';
    public static string $CORRECTION_OBLIGATOIRE_INTERVAL_TO_STRING = '3 mois';
    public static string $CORRECTION_FACULTATIVE_INTERVAL = 'P2M';
    public static string $CORRECTION_FACULTATIVE_INTERVAL_TO_STRING = '2 mois';

    public static string $CORRECTION_SURSIS_INTERVAL = 'P7D';
    public static string $CORRECTION_SURSIS_INTERVAL_TO_STRING = '1 semaine';

    public static ?string $CORRECTION_AUTORISEE_FORCAGE_NON = null; // pas de forçage
    public static string $CORRECTION_AUTORISEE_FORCAGE_AUCUNE = 'aucune'; // aucune correction autorisée
    public static string $CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE = 'obligatoire'; // corrections obligatoires autorisées
    public static string $CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE = 'facultative'; // corrections facultatives autorisées


    private ?string $correctionAutorisee = null;
    private ?string $correctionAutoriseeForcee = null;
    private ?DateTime $dateButoirDepotVersionCorrigeeAvecSursis = null;
    private ?string $correctionEffectuee = null;


    /**
     * Indique si le témoin indique que des corrections sont autorisées.
     * NB: Par défaut le forçage du témoin est pris en compte
     *
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return bool
     */
    public function isCorrectionAutorisee(bool $prendreEnCompteLeForcage = true): bool
    {
        return (bool) $this->getCorrectionAutorisee($prendreEnCompteLeForcage);
    }

    /**
     * Indique si le témoin de corrections autorisées fait l'objet d'un forçage.
     *
     * @return bool
     */
    public function isCorrectionAutoriseeForcee(): bool
    {
        return $this->getCorrectionAutoriseeForcee() !== null;
    }

    /**
     * Retourne la valeur du témoin de corrections autorisées.
     * NB: Par défaut le forçage du témoin est pris en compte.
     *
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return string|null 'facultative' ou 'obligatoire' ou null
     *
     * @see getCorrectionAutoriseeForcee()
     */
    public function getCorrectionAutorisee(bool $prendreEnCompteLeForcage = true): ?string
    {
        if ($prendreEnCompteLeForcage === false) {
            return $this->correctionAutorisee;
        }

        if ($this->getCorrectionAutoriseeForcee() === self::$CORRECTION_AUTORISEE_FORCAGE_AUCUNE) {
            // si le forçage est à 'aucune', alors aucune correction autorisée!
            return null;
        }

        return $this->getCorrectionAutoriseeForcee() ?: $this->correctionAutorisee;
    }

    /**
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return bool
     */
    public function getCorrectionAutoriseeEstFacultative(bool $prendreEnCompteLeForcage = true): bool
    {
        return $this->getCorrectionAutorisee($prendreEnCompteLeForcage) === self::$CORRECTION_AUTORISEE_FACULTATIVE;
    }

    /**
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return bool
     */
    public function getCorrectionAutoriseeEstObligatoire(bool $prendreEnCompteLeForcage = true): bool
    {
        return $this->getCorrectionAutorisee($prendreEnCompteLeForcage) === self::$CORRECTION_AUTORISEE_OBLIGATOIRE;
    }

    /**
     * @param bool $plural
     * @param bool $prendreEnCompteLeForcage Faut-il prendre en compte le forçage éventuel ?
     * @return string
     */
    public function getCorrectionAutoriseeToString(bool $plural, bool $prendreEnCompteLeForcage = true): string
    {
        $correctionAutorisee = $this->getCorrectionAutorisee($prendreEnCompteLeForcage);

        return $plural ?
            self::$correctionsLibellesPluriels[$correctionAutorisee] :
            self::$correctionsLibelles[$correctionAutorisee];
    }

    /**
     * Change la valeur du témoin de corrections autorisées importé.
     * NB: cette méthode ne devrait pas être utilisée, sauf pour les tests unitaires.
     *
     * @param string|null $correctionAutorisee
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setCorrectionAutorisee(string $correctionAutorisee = null): self
    {
        Assertion::inArray($correctionAutorisee, [
            null,
            self::$CORRECTION_AUTORISEE_FACULTATIVE,
            self::$CORRECTION_AUTORISEE_OBLIGATOIRE,
        ]);

        $this->correctionAutorisee = $correctionAutorisee;

        return $this;
    }

    /**
     * Retourne la valeur du forçage du témoin de corrections autorisées.
     *
     * @return string|null
     */
    public function getCorrectionAutoriseeForcee(): ?string
    {
        return $this->correctionAutoriseeForcee;
    }

    /**
     * Modifie la valeur du forçage du témoin de corrections autorisées.
     *
     * @param string|null $correctionAutoriseeForcee
     * @return self
     * @throws \Assert\AssertionFailedException
     */
    public function setCorrectionAutoriseeForcee(string $correctionAutoriseeForcee = null): self
    {
        Assertion::inArray($correctionAutoriseeForcee, [
            self::$CORRECTION_AUTORISEE_FORCAGE_NON,
            self::$CORRECTION_AUTORISEE_FORCAGE_AUCUNE,
            self::$CORRECTION_AUTORISEE_FORCAGE_FACULTATIVE,
            self::$CORRECTION_AUTORISEE_FORCAGE_OBLIGATOIRE,
        ]);

        $this->correctionAutoriseeForcee = $correctionAutoriseeForcee;

        return $this;
    }

    /**
     * @return string
     */
    public function getCorrectionEffectuee()
    {
        return $this->correctionEffectuee;
    }

    /**
     * @param string $correctionEffectuee
     * @return self
     */
    public function setCorrectionEffectuee(string $correctionEffectuee): self
    {
        $this->correctionEffectuee = $correctionEffectuee;
        return $this;
    }



    /**
     * @return null|DateTime
     */
    public function getDateButoirDepotVersionCorrigeeAvecSursis(): ?DateTime
    {
        return $this->dateButoirDepotVersionCorrigeeAvecSursis;
    }

    /**
     * @return string
     */
    public function getDateButoirDepotVersionCorrigeeAvecSursisToString(): string
    {
        return $this->dateButoirDepotVersionCorrigeeAvecSursis ?
            $this->dateButoirDepotVersionCorrigeeAvecSursis->format(Constants::DATE_FORMAT) :
            '';
    }

    /**
     * @param \DateTime $date
     * @return self
     */
    public function setDateButoirDepotVersionCorrigeeAvecSursis(DateTime $date): self
    {
        $this->dateButoirDepotVersionCorrigeeAvecSursis = $date;
        return $this;
    }

    /**
     * @return self
     */
    public function unsetDateButoirDepotVersionCorrigeeAvecSursis(): self
    {
        $this->dateButoirDepotVersionCorrigeeAvecSursis = null;
        return $this;
    }

    public function getDateButoirDepotVersionCorrigeeFromDateSoutenance(DateTime $dateSoutenance): DateTime
    {
        if ($this->getCorrectionAutorisee() === null) {
            throw new BadMethodCallException("Appel de " . __METHOD__ . " illogique car aucune correction attendue");
        }

        $intervalSpec = $this->getDelaiDepotVersionCorrigeeInterval();

        $date = clone $dateSoutenance;
        $date
            ->add(new DateInterval($intervalSpec))
            ->setTime(0, 0, 0);

        return $date;
    }

    public function getDateButoirDepotVersionCorrigeeFromDateSoutenanceToString(DateTime $dateSoutenance): string
    {
        return $this->getDateButoirDepotVersionCorrigeeFromDateSoutenance($dateSoutenance)->format(Constants::DATE_FORMAT);
    }

    public function getDelaiDepotVersionCorrigeeInterval(): string
    {
        switch ($val = $this->getCorrectionAutorisee()) {
            case self::$CORRECTION_AUTORISEE_OBLIGATOIRE:
                return static::$CORRECTION_OBLIGATOIRE_INTERVAL; // + 3 mois
            case self::$CORRECTION_AUTORISEE_FACULTATIVE:
                return static::$CORRECTION_FACULTATIVE_INTERVAL; // + 2 mois
            default:
                throw new RuntimeException("Valeur de correction attendue non prévue: " . $val);
        }
    }

    public function getDelaiDepotVersionCorrigeeToString(): string
    {
        switch ($spec = $this->getDelaiDepotVersionCorrigeeInterval()) {
            case self::$CORRECTION_OBLIGATOIRE_INTERVAL:
                return static::$CORRECTION_OBLIGATOIRE_INTERVAL_TO_STRING;
            case self::$CORRECTION_FACULTATIVE_INTERVAL:
                return static::$CORRECTION_FACULTATIVE_INTERVAL_TO_STRING;
            default:
                throw new RuntimeException("Interval rencontré non prévu: " . $spec);
        }
    }

    /**
     * Détermine si la date butoir de dépôt de la verison corrigée est dépassée ou non, selon la date de soutenance
     * spécifiée.
     *
     * NB : Synthèse de
     * {@see isDateButoirDepotVersionCorrigeeFromDateSoutenanceDepassee()} et
     * {@see isDateButoirDepotVersionCorrigeeAvecSursisDepassee()}
     *
     * @param \DateTime|null $dateSoutenance
     * @return bool
     */
    public function isDateButoirDepotVersionCorrigeeDepassee(?DateTime $dateSoutenance = null): bool
    {
        $dateButoirFromSoutenanceDepassee =
            $dateSoutenance &&
            $this->isDateButoirDepotVersionCorrigeeFromDateSoutenanceDepassee($dateSoutenance);
        if (! $dateButoirFromSoutenanceDepassee) {
            return false;
        }

        if ($this->dateButoirDepotVersionCorrigeeAvecSursis !== null) {
            return $this->isDateButoirDepotVersionCorrigeeAvecSursisDepassee();
        }

        return true;
    }

    /**
     * Détermine si la date butoir de dépôt de la verison corrigée DEDUITE DE LA DATE DE SOUTENANCE est dépassée ou non.
     *
     * NB : La date de soutenance est demandée en argument pour bien marquer le fait qu'une date de soutenance non null
     * est requise.
     *
     * @param \DateTime $dateSoutenance
     * @return bool
     */
    public function isDateButoirDepotVersionCorrigeeFromDateSoutenanceDepassee(DateTime $dateSoutenance): bool
    {
        $dateButoir = $this->getDateButoirDepotVersionCorrigeeFromDateSoutenance($dateSoutenance);
        $today = new \DateTime('today');

        return $today > $dateButoir;
    }

    /**
     * Détermine si la date butoir de dépôt de la verison corrigée AVEC SURSIS UNQUEMENT est dépassée ou non.
     *
     * @return bool
     */
    public function isDateButoirDepotVersionCorrigeeAvecSursisDepassee(): bool
    {
        if ($this->dateButoirDepotVersionCorrigeeAvecSursis === null) {
            throw new BadMethodCallException("Appel de " . __METHOD__ . " illogique car aucun sursis n'a été accordé");
        }

        $today = new \DateTime('today');

        return $today > $this->dateButoirDepotVersionCorrigeeAvecSursis;
    }

    /**
     * Applique à la date de soutenance spécifiée le sursis (délai) de dépôt de la version corrigée.
     *
     * @param \DateTime $dateSoutenance
     * @return DateTime
     */
    public function computeDateButoirDepotVersionCorrigeeAvecSursis(DateTime $dateSoutenance): DateTime
    {
        $today = new \DateTime('today');
        $date = $dateSoutenance < $today ? $today : clone $dateSoutenance;

        return $date
            ->add(new DateInterval(self::$CORRECTION_SURSIS_INTERVAL))
            ->setTime(0, 0);
    }
}