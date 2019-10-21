<?php

namespace Import\Model;

/**
 * TmpDoctorant
 */
class TmpDoctorant
{
    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $etablissementId;

    /**
     * @var string
     */
    private $individuId;

    /**
     * @var string
     */
    private $ine;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getIndividuId()
    {
        return $this->individuId;
    }
}

