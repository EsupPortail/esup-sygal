<?php

namespace Import\Model;

class TmpActeur {
    protected $id;
    protected $sourceId;
    protected $etablissementId;
    protected $individuId;
    protected $theseId;
    protected $roleId;
    protected $libQualite;
    protected $codeQualite;
    protected $libEtablissement;
    protected $codeEtablissement;
    protected $codeRoleJury;
    protected $libRoleJury;
    protected $temoinHDR;
    protected $temoinRapport;
    protected $sourceCode;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
        $this->sourceId                     = $json->{'sourceId'};
        $this->individuId                   = $json->{'individuId'};
        $this->theseId                      = $json->{'theseId'};
        $this->roleId                       = $json->{'roleId'};
        $this->libQualite                   = $json->{'libQualite'};
        $this->codeQualite                  = $json->{'codeQualite'};
        $this->libEtablissement             = $json->{'libEtablissement'};
        $this->codeEtablissement            = $json->{'codeEtablissement'};
        $this->codeRoleJury                 = $json->{'codeRoleJury'};
        $this->libRoleJury                  = $json->{'libRoleJury'};
        $this->temoinHDR                    = $json->{'temoinHDR'};
        $this->temoinRapport                = $json->{'temoinRapport'};
    }

}