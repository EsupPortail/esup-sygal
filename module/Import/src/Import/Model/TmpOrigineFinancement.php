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
     * @var \DateTime
     */
    private $sourceInsertDate;
}