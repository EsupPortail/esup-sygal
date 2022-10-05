<?php

namespace Application\Entity\Db;

class Discipline {

    private string $code;
    private string $libelle;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

}
