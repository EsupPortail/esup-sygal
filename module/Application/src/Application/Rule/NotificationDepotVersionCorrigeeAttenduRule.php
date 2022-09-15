<?php

namespace Application\Rule;

use Application\Constants;
use These\Entity\Db\These;
use DateInterval;
use DateTime;
use DomainException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * Règle concernant notification au sujet du dépôt attendu de la version corrigée.
 *
 * @author Unicaen
 */
class NotificationDepotVersionCorrigeeAttenduRule implements RuleInterface
{
    use MessageAwareTrait;

    const SPEC_INTERVAL_ENTRE_DATE_NOTIF_ET_BUTOIRE = 'P1M'; // 1 mois

    /**
     * @var These
     */
    private $these;

    /**
     * @var DateTime|null
     */
    private $dateDerniereNotif;

    /**
     * @var DateTime|null
     */
    private $dateProchaineNotif;

    /**
     * @var bool
     */
    private $estPremiereNotif;

    /**
     * @var DateTime
     */
    private $dateAujourdhui;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAujourdhui(new DateTime());
    }

    /**
     * @param These $these
     * @return NotificationDepotVersionCorrigeeAttenduRule
     */
    public function setThese($these)
    {
        if ($these->getCorrectionAutorisee() === null) {
            throw new DomainException("Thèse spécifiée invalide car aucune correction attendue");
        }
        if ($these->getDateSoutenance() === null) {
            throw new DomainException("Thèse spécifiée invalide car date de soutenance inconnue");
        }

        $this->these = $these;

        return $this;
    }

    /**
     * @param DateTime|null $dateDerniereNotif
     * @return NotificationDepotVersionCorrigeeAttenduRule
     */
    public function setDateDerniereNotif($dateDerniereNotif)
    {
        $this->dateDerniereNotif = $dateDerniereNotif;

        if ($this->dateDerniereNotif !== null) {
            $this->dateDerniereNotif->setTime(0, 0, 0);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function execute(): self
    {
        $dateButoir = $this->these->getDateButoirDepotVersionCorrigeeFromDateSoutenance($this->these->getDateSoutenance());
        $dateButoir->setTime(0, 0, 0);

        // si la date butoir est dépassée, pas de notif
        if ($this->dateAujourdhui > $dateButoir) {
            $this->dateProchaineNotif = null;
            $this->estPremiereNotif = $this->dateDerniereNotif === null;
            $this->addMessage("Plus la peine de notifier, la date butoir est dépassée.");
            return $this;
        }

        // La 1ere notification se fait immédiatement
        if ($this->dateDerniereNotif === null) {
            $this->dateProchaineNotif = $this->dateAujourdhui;
            $this->estPremiereNotif = true;
            $this->addMessage("Première notification.");
            return $this;
        }

        $this->estPremiereNotif = false;

        switch ($this->these->getCorrectionAutorisee()) {
            case These::CORRECTION_AUTORISEE_OBLIGATOIRE:
                $spec = self::SPEC_INTERVAL_ENTRE_DATE_NOTIF_ET_BUTOIRE;
                try {
                    $interval = new DateInterval($spec);
                } catch (\Exception $e) {
                    throw new RuntimeException("Interval invalide : $spec", null, $e);
                }
                $dateProchaineNotif = $dateButoir->sub($interval); // Date butoir - interval
                break;
            case These::CORRECTION_AUTORISEE_FACULTATIVE:
                // Une seule notification pour des corrections facultatives
                $dateProchaineNotif = null;
                break;
            default:
                // pas possible
                $dateProchaineNotif = null;
                break;
        }

        // Pas de date de prochaine notification !
        if ($dateProchaineNotif === null) {
            $this->dateProchaineNotif = null;
            return $this;
        }

        $dateProchaineNotif->setTime(0, 0, 0);

        // La date de prochaine notif est passée: plus de notif.
        if ($dateProchaineNotif < $this->dateAujourdhui) {
            $this->dateProchaineNotif = null;
            $this->addMessage("Plus de notification nécessaire.");
            return $this;
        }
        // La date de prochaine notif égale la date de dernière notif: les notifications sont terminées.
        if ($dateProchaineNotif == $this->dateDerniereNotif) {
            $this->dateProchaineNotif = null;
            $this->addMessage("Les notifications sont terminées.");
            return $this;
        }

        $this->dateProchaineNotif = $dateProchaineNotif;
        $this->addMessage(sprintf("Prochaine notification le %s.", $this->dateProchaineNotif->format(Constants::DATETIME_FORMAT)));

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateProchaineNotif()
    {
        return $this->dateProchaineNotif;
    }

    /**
     * @return bool
     */
    public function estPremiereNotif()
    {
        return $this->estPremiereNotif;
    }

    /**
     * Permet de voyager dans le temps en déplaçant la date d'aujourdhui.
     *
     * @param DateTime $dateAujourdhui
     * @return static
     */
    public function setDateAujourdhui(DateTime $dateAujourdhui)
    {
        $this->dateAujourdhui = $dateAujourdhui;
        $this->dateAujourdhui->setTime(0, 0, 0);

        return $this;
    }


}