<?php

namespace Application\Entity\Db;

class ImportObservEtab
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var ImportObserv
     */
    private $importObserv;

    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param ImportObserv $importObserv
     * @return self
     */
    public function setImportObserv($importObserv)
    {
        $this->importObserv = $importObserv;

        return $this;
    }

    /**
     * @return ImportObserv
     */
    public function getImportObserv()
    {
        return $this->importObserv;
    }

    /**
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     * @return ImportObservEtab
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

}
