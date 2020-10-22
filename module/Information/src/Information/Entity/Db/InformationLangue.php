<?php

namespace Information\Entity\Db;

class  InformationLangue {

    /** @var string */
    private $id;

    /** @var string */
    private $libelle;

    /** @var string */
    private $drapeau;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @return string
     */
    public function getDrapeau(): string
    {
        return $this->drapeau;
    }
}