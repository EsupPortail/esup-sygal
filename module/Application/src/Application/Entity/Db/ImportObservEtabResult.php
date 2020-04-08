<?php

namespace Application\Entity\Db;

use DateTime;

class ImportObservEtabResult
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ImportObservEtab
     */
    private $importObservEtab;

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
     * @var bool
     */
    private $tooOld;

    /**
     * @param ImportObservEtab $importObservEtab
     * @return ImportObservEtabResult
     */
    public function setImportObservEtab($importObservEtab)
    {
        $this->importObservEtab = $importObservEtab;

        return $this;
    }

    /**
     * @param DateTime $dateCreation
     * @return ImportObservEtabResult
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @param string $sourceCode
     * @return ImportObservEtabResult
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * @param string $resultat
     * @return ImportObservEtabResult
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
     * @return ImportObservEtab
     */
    public function getImportObservEtab()
    {
        return $this->importObservEtab;
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
        switch ($this->getImportObservEtab()->getImportObserv()->getTableName()) {
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
        switch ($this->getImportObservEtab()->getImportObserv()->getColumnName()) {
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
     * @return ImportObservEtabResult
     */
    public function setDateNotif(DateTime $dateNotif = null)
    {
        $this->dateNotif = $dateNotif;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTooOld()
    {
        return $this->tooOld;
    }

    /**
     * @param bool $tooOld
     * @return ImportObservEtabResult
     */
    public function setTooOld($tooOld)
    {
        $this->tooOld = $tooOld;
        return $this;
    }

}
