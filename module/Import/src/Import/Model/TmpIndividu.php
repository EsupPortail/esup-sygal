<?php

namespace Import\Model;

class TmpIndividu {
    protected $id;
    protected $sourceId;
    protected $type;
    protected $civilite;
    protected $nomUsuel;
    protected $nomPatronymique;
    protected $prenom1;
    protected $prenom2;
    protected $prenom3;
    protected $email;
    protected $dateNaissance;
    protected $nationalite;
    protected $etablissementId;
    protected $sourceCode;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
        $this->sourceId                     = $json->{'sourceId'};
        $this->type                         = $json->{'type'};
        $this->civilite                     = $json->{'civilite'};
        $this->nomUsuel                     = $json->{'nomUsuel'};
        $this->nomPatronymique              = $json->{'nomPatronymique'};
        $this->prenom1                      = $json->{'prenom1'} ? : "Aucun";
        $this->prenom2                      = $json->{'prenom2'};
        $this->prenom3                      = $json->{'prenom3'};
        $this->email                        = $json->{'email'};
        $this->dateNaissance                = ($json->{'dateNaissance'} !== null && $json->{'dateNaissance'}->{'date'} !== null) ? new \DateTime($json->{'dateNaissance'}->{'date'}) : null ;
        $this->nationalite                  = $json->{'nationalite'};
    }

}