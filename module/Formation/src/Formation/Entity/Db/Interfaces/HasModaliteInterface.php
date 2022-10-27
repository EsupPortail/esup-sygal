<?php

namespace Formation\Entity\Db\Interfaces;

interface HasModaliteInterface {

    const MODALITE_PRESENTIEL   = 'P';
    const MODALITE_DISTANCIEL   = 'D';
    const MODALITE_MIXTE        = 'M';

    const MODALITES = [
        self::MODALITE_PRESENTIEL => "Présentiel",
        self::MODALITE_DISTANCIEL => "Distanciel",
        self::MODALITE_MIXTE => "Présentiel et distanciel",
    ];

    public function getModalite() : ?string;
    public function setModalite(?string $modalite) : HasModaliteInterface;

}