<?php

namespace Import\Model;

class TmpUniteRecherche {

    protected $id;
    protected $etablissementId;
    protected $sourceId;
    protected $sourceCode;
    protected $structureId;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
//        protected $etablissementId;
        $this->sourceId                     = $json->{'sourceId'};
        $this->sourceCode                   = $json->{'structureId'};
        $this->structureId                  = $json->{'structureId'};
    }

}