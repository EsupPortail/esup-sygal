<?php

namespace Import\Model;

class TmpStructure {

    protected $id;
    protected $etablissementId;
    protected $sourceId;
    protected $code;
    protected $typeStructureId;
    protected $sigle;
    protected $libelle;
    protected $codePays;
    protected $libellePays;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
//        protected $etablissementId;
        $this->sourceId                     = $json->{'sourceId'};
        $this->code                         = $json->{'code'};
        $this->typeStructureId              = $json->{'typeStructureId'};
        $this->sigle                        = $json->{'sigle'};
        $this->libelle                      = $json->{'libelle'};
        $this->codePays                     = $json->{'codePays'};
        $this->libellePays                  = $json->{'libellePays'};
    }

}