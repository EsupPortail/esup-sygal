<?php

namespace Import\Model;

/**
 * TmpFinancement
 */
class TmpFinancement
{
    protected $id;
    protected $sourceId;
    protected $etablissementId;
    protected $sourceCode;
    protected $theseId;
    protected $annee;
    protected $origineFinancementId;
    protected $complementFinancement;
    protected $quotiteFinancement;
    protected $dateDebutFinancement;
    protected $dateFinFinancement;
    protected $codeTypeFinancement;
    protected $libelleTypeFinancement;

    /**
     * @var \DateTime
     */
    private $sourceInsertDate;
}