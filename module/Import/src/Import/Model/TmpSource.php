<?php

namespace Import\Model;

class TmpSource
{
    protected $id;
    protected $code;
    protected $libelle;
    protected $importable;
    protected $etablissementId;
    protected $sourceCode;

    public function __construct($json)
    {
        $this->id = $json->{'id'};
        $this->code = $json->{'code'};
        $this->libelle = $json->{'libelle'};
        $this->importable = $json->{'importable'};
    }

}