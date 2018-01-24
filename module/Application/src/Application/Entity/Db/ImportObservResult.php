<?php

namespace Application\Entity\Db;

use DateTime;

class ImportObservResult
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ImportObserv
     */
    private $importObserv;

    /**
     * @var DateTime
     */
    private $dateCreation;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $resultat;

    /**
     * @var DateTime
     */
    private $dateNotif;

    /**
     * @param ImportObserv $importObserv
     * @return ImportObservResult
     */
    public function setImportObserv($importObserv)
    {
        $this->importObserv = $importObserv;

        return $this;
    }

    /**
     * @param DateTime $dateCreation
     * @return ImportObservResult
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @param string $sourceCode
     * @return ImportObservResult
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * @param string $resultat
     * @return ImportObservResult
     */
    public function setResultat($resultat)
    {
        $this->resultat = $resultat;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ImportObserv
     */
    public function getImportObserv()
    {
        return $this->importObserv;
    }

    /**
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     */
    public function getResultat()
    {
        return $this->resultat;
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
    public function getDateNotif()
    {
        return $this->dateNotif;
    }

    /**
     * @param DateTime $dateNotif
     * @return ImportObservResult
     */
    public function setDateNotif(DateTime $dateNotif = null)
    {
        $this->dateNotif = $dateNotif;

        return $this;
    }
}
