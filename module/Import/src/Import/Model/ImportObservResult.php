<?php

namespace Import\Model;

use These\Entity\Db\These;
use DateTime;
use UnicaenDbImport\Entity\Db\AbstractImportObserv;

class ImportObservResult extends \UnicaenDbImport\Entity\Db\ImportObservResult
{
    /**
     * @var DateTime
     */
    protected $dateLimiteNotif;

    /**
     * @var ImportObserv
     */
    protected $importObserv;

    /**
     * @param ImportObserv $importObserv
     */
    public function setImportObserv(AbstractImportObserv $importObserv)
    {
        $this->importObserv = $importObserv;
    }

    /**
     * @return ImportObserv
     */
    public function getImportObserv(): AbstractImportObserv
    {
        return $this->importObserv;
    }

    /**
     * @return string
     */
    public function getResultatToString()
    {
        switch ($this->getImportObserv()->getTableName()) {
            case 'THESE':
                return $this->getResultatToStringForThese();
            default:
                return $this->getResultat();
        }
    }

    /**
     * @return string
     */
    private function getResultatToStringForThese()
    {
        switch ($this->getImportObserv()->getColumnName()) {
            case 'RESULTAT':
                $values = explode('>', $this->getResultat());
                $values[0] = $values[0] <> "" ? These::$resultatsLibellesLongs[$values[0]] : "Aucun";
                $values[1] = $values[1] <> "" ? These::$resultatsLibellesLongs[$values[1]] : "Aucun";
                return "Résultat: $values[0] => $values[1]";
            case 'CORREC_AUTORISEE':
                $values = explode('>', $this->getResultat());
                $values[0] = $values[0] <> "" ? These::$correctionsLibelles[$values[0]] : "Aucune";
                $values[1] = $values[1] <> "" ? These::$correctionsLibelles[$values[1]] : "Aucune";
                return "Correction attendue: $values[0] => $values[1]";
            default:
                return $this->getResultat();
        }
    }

    /**
     * @return DateTime
     */
    public function getDateLimiteNotif(): DateTime
    {
        // Solution provisoire pour avoir un moyen de ne pas notifier si l'observation est trop vieille.
        if ($this->dateLimiteNotif === null) {
            $this->dateLimiteNotif = $this->dateCreation->add(new \DateInterval('P1D')); // 1 jour
        }
        //

        return $this->dateLimiteNotif;
    }

    /**
     * @param DateTime|null $dateLimiteNotif
     * @return self
     */
    public function setDateLimiteNotif(?DateTime $dateLimiteNotif = null): self
    {
        $this->dateLimiteNotif = $dateLimiteNotif;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDateLimiteNotifDepassee(): bool
    {
        if ($this->getDateLimiteNotif() === null) {
            return false;
        }

        return date_create() > $this->getDateLimiteNotif();
    }
}
