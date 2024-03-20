<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareTrait;

class DomaineHal
{

    use HistoriqueAwareTrait;

    /**
     * @var int
     */
    private $docId;

    /**
     * @var bool
     */
    private $haveNextBool;

    /**
     * @var string
     */
    private $codeS;

    /**
     * @var string
     */
    private $frDomainS;

    /**
     * @var string
     */
    private $enDomainS;

    /**
     * @var int
     */
    private $levelI;

    /**
     * @var int
     */
    private $id;

    /**
     * @var DomaineHal
     */
    private $parentId;

   /**
     * Set docId.
     *
     * @param int $docId
     *
     * @return DomaineHal
     */
    public function setDocId($docId)
    {
        $this->docId = $docId;

        return $this;
    }

    /**
     * Get docId.
     *
     * @return int
     */
    public function getDocId()
    {
        return $this->docId;
    }

    /**
     * Set haveNextBool.
     *
     * @param bool $haveNextBool
     *
     * @return DomaineHal
     */
    public function setHaveNextBool($haveNextBool)
    {
        $this->haveNextBool = $haveNextBool;

        return $this;
    }

    /**
     * Get haveNextBool.
     *
     * @return bool
     */
    public function getHaveNextBool()
    {
        return $this->haveNextBool;
    }

    /**
     * Set codeS.
     *
     * @param string $codeS
     *
     * @return DomaineHal
     */
    public function setCodeS($codeS)
    {
        $this->codeS = $codeS;

        return $this;
    }

    /**
     * Get codeS.
     *
     * @return string
     */
    public function getCodeS()
    {
        return $this->codeS;
    }

    /**
     * Set frDomainS.
     *
     * @param string $frDomainS
     *
     * @return DomaineHal
     */
    public function setFrDomainS($frDomainS)
    {
        $this->frDomainS = $frDomainS;

        return $this;
    }

    /**
     * Get frDomainS.
     *
     * @return string
     */
    public function getFrDomainS()
    {
        return $this->frDomainS;
    }

    /**
     * Set enDomainS.
     *
     * @param string $enDomainS
     *
     * @return DomaineHal
     */
    public function setEnDomainS($enDomainS)
    {
        $this->enDomainS = $enDomainS;

        return $this;
    }

    /**
     * Get enDomainS.
     *
     * @return string
     */
    public function getEnDomainS()
    {
        return $this->enDomainS;
    }

    /**
     * Set levelI.
     *
     * @param int $levelI
     *
     * @return DomaineHal
     */
    public function setLevelI($levelI)
    {
        $this->levelI = $levelI;

        return $this;
    }

    /**
     * Get levelI.
     *
     * @return int
     */
    public function getLevelI()
    {
        return $this->levelI;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parentId.
     *
     * @param DomaineHal|null $parentId
     *
     * @return DomaineHal
     */
    public function setParentId(DomaineHal $parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return DomaineHal|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }
}
