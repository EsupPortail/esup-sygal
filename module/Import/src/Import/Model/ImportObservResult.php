<?php

namespace Import\Model;

use Application\Entity\Db\These;
use DateTime;

class ImportObservResult extends \UnicaenDbImport\Entity\Db\ImportObservResult
{
    /**
     * @var DateTime
     */
    protected $dateNotif;

    /**
     * @var DateTime
     */
    protected $dateLimiteNotif;

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
     * @return DateTime|null
     */
    public function getDateNotif(): ?DateTime
    {
        return $this->dateNotif;
    }

    /**
     * @param DateTime|null $dateNotif
     * @return self
     */
    public function setDateNotif(?DateTime $dateNotif = null): self
    {
        $this->dateNotif = $dateNotif;
        return $this;
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
