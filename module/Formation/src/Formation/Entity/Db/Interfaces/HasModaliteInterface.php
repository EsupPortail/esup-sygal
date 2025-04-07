<?php

namespace Formation\Entity\Db\Interfaces;

interface HasModaliteInterface {

    public const MODALITE_PRESENTIEL   = 'P';
    public const MODALITE_DISTANCIEL   = 'D';
    public const MODALITE_MIXTE        = 'M';

    public const MODALITES = [
        self::MODALITE_PRESENTIEL => "Présentiel",
        self::MODALITE_DISTANCIEL => "Distanciel",
        self::MODALITE_MIXTE => "Présentiel et distanciel",
    ];

    public function getModalite() : ?string;
    public function setModalite(?string $modalite) : HasModaliteInterface;

}