<?php

namespace Formation\Entity\Db\Interfaces;

interface HasModaliteInterface {

    const MODALITE_PRESENTIEL   = 'P';
    const MODALITE_DISTANCIEL   = 'D';

    const MODALITES = [
        self::MODALITE_PRESENTIEL => "Présentiel",
        self::MODALITE_DISTANCIEL => "Distanciel",
    ];

    public function getModalite() : ?string;
    public function setModalite(?string $modalite) : HasModaliteInterface;

}