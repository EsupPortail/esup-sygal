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
     * @var string
     */
    private $sourceInsertDate;

    /**
     * @return \DateTime
     */
    public function getIndividuId()
    {
        return $this->individuId;
    }
}

