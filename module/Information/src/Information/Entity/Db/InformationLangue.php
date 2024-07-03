<?php

namespace Information\Entity\Db;

class InformationLangue
{
    const ID_LANGUE_PAR_DEFAUT = 'FR';

    /** @var string */
    private $id;

    /** @var string */
    private $libelle;

    /** @var string */
    private $drapeau;

    public function getId(): string
    {
        return $this->id;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function getDrapeau(): string
    {
        return $this->drapeau;
    }
}