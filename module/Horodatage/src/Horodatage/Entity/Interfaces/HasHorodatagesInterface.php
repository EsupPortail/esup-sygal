<?php

namespace Horodatage\Entity\Interfaces;

use Horodatage\Entity\Db\Horodatage;

interface HasHorodatagesInterface {

    public function getHorodatages(?string $type = null, ?string $complement = null) : array;
    public function getLastHoradatage(?string $type = null, ?string $complement = null) : ?Horodatage;
    public function addHorodatage(Horodatage $horodatage) : void;
}