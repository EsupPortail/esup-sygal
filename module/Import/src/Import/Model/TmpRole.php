<?php

namespace Import\Model;

class TmpRole {
    protected $id;
    protected $sourceId;
    protected $etablissementId;
    protected $libLongRole;
    protected $libCourtRole;
    protected $sourceCode;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
        $this->sourceId                     = $json->{'sourceId'};
        $this->libLongRole                  = $json->{'libLongRole'};
        $this->libCourtRole                 = $json->{'libCourtRole'};
    }

}