<?php

namespace Import\Model;

class TmpVariable {

    protected $id;
    protected $sourceId;
    protected $etablissementId;
    protected $libEtablissement;
    protected $libResponsable;
    protected $libTitre;
    protected $sourceCode;
    protected $dateDebValidite;
    protected $dateFinValidite;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
        $this->sourceId                     = $json->{'sourceId'};
        $this->libEtablissement             = $json->{'libEtablissement'};
        $this->libResponsable               = $json->{'libResponsable'};
        $this->libTitre                     = $json->{'libTitre'};
        $this->dateDebValidite              = new \DateTime($json->{'dateDebValidite'}->{'date'});
        $this->dateFinValidite              = new \DateTime($json->{'dateFinValidite'}->{'date'});
    }

}