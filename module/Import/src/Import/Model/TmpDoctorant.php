<?php

namespace Import\Model;

/**
 * Class TmpDoctorant
 * @package Application\Model
 * Cette classe sert de tampon entre le Web Service de chaque établissement et
 * le SGBD de l'application.
 * /!\ $etablissementId doit être ajouter 'manuellement'
 */


class TmpDoctorant {
    protected $id;
    protected $sourceId;
    protected $etablissementId;
    protected $individuId;
    protected $sourceCode;

    public function __construct($json) {
        $this->id                           = $json->{'id'};
        $this->sourceId                     = $json->{'sourceId'};
        $this->individuId                   = $json->{'individuId'};
    }

}