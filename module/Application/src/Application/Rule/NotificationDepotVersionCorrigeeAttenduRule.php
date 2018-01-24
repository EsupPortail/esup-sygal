<?php

namespace Application\Rule;

use Application\Entity\Db\These;
use DateInterval;
use DateTime;

/**
 * Règle concernant notification au sujet du dépôt attendu de la version corrigée.
 *
 * @author Unicaen
 */
class NotificationDepotVersionCorrigeeAttenduRule implements RuleInterface
{
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
    public function execute()
    {
        $dateButoir = $this->these->getDateButoirDepotVersionCorrigee();
        if ($dateButoir === null) {
            $this->dateProchaineNotif = null;
            $this->estPremiereNotif = $this->dateDerniereNotif === null;
            return $this;
        }

        $dateButoir->setTime(0, 0, 0);

        // si la date butoir est dépassée, pas de notif
        if ($this->dateAujourdhui > $dateButoir) {
            $this->dateProchaineNotif = null;
            $this->estPremiereNotif = $this->dateDerniereNotif === null;
            return $this;
        }

        // La 1ere notification se fait immédiatement
        if ($this->dateDerniereNotif === null) {
            $this->dateProchaineNotif = $this->dateAujourdhui;
            $this->estPremiereNotif = true;
            return $this;
        }

        $this->estPremiereNotif = false;

        switch ($this->these->getCorrectionAutorisee()) {
            case These::CORRECTION_MAJEURE:
                if ($dateButoir !== null) {
                    $spec = self::SPEC_INTERVAL_ENTRE_DATE_NOTIF_ET_BUTOIRE;
                    $dateProchaineNotif = $dateButoir->sub(new DateInterval($spec)); // Date butoir - interval
                }
                else {
                    $dateProchaineNotif = null;
                }
                break;
            case These::CORRECTION_MINEURE:
                $dateProchaineNotif = null;
                break;
            default:
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
            return $this;
        }
        // La date de prochaine notif égale la date de dernière notif: les notifications sont terminées.
        if ($dateProchaineNotif == $this->dateDerniereNotif) {
            $this->dateProchaineNotif = null;
            return $this;
        }

        $this->dateProchaineNotif = $dateProchaineNotif;

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