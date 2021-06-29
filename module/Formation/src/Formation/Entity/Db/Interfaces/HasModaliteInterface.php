<?php

namespace Formation\Entity\Db\Interfaces;

interface HasModaliteInterface {

    const MODALITE_PRESENTIEL   = 'P';
    const MODALITE_DISTANCIEL   = 'D';

    public function getModalite() : ?string;
    public function setModalite(?string $modalite) : HasModaliteInterface;

}