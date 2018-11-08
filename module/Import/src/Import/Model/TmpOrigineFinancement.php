<?php

namespace Import\Model;

/**
 * TmpOrigineFinancement
 */
class TmpOrigineFinancement
{
    protected $id;
    protected $sourceId;
    protected $etablissementId;
    protected $sourceCode;
    protected $codOfi;
    protected $licOfi;
    protected $libOfi;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * @return mixed
     */
    public function getEtablissementId()
    {
        return $this->etablissementId;
    }

    /**
     * @return mixed
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return mixed
     */
    public function getCodOfi()
    {
        return $this->codOfi;
    }

    /**
     * @return mixed
     */
    public function getLicOfi()
    {
        return $this->licOfi;
    }

    /**
     * @return mixed
     */
    public function getLibOfi()
    {
        return $this->libOfi;
    }
}