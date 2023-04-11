<?php

namespace Horodatage\Entity\Traits;

use Doctrine\Common\Collections\Collection;
use Horodatage\Entity\Db\Horodatage;

trait HasHorodatagesTrait {

    private Collection $horodatages;

    /** @return Horodatage[] */
    public function getHorodatages(?string $type = null, ?string $complement = null) : array
    {
        $result = [];
        /** @var Horodatage $horadatage */
        foreach ($this->horodatages as $horadatage) {
            if (    ($type === null OR $horadatage->getType() === $type)
                AND ($complement === null OR $horadatage->getComplement() === $complement)
            ) {
                $result[] = $horadatage;
            }
        }
        usort($result, function(Horodatage $a, Horodatage $b) { return $a->getDate() > $b->getDate();});
        return $result;
    }

    public function getLastHoradatage(?string $type = null, ?string $complement = null) : ?Horodatage
    {
        $horodatages = $this->getHorodatages($type, $complement);
        if ($horodatages === []) return null;
        return array_reverse($horodatages)[0];
    }

    public function addHorodatage(Horodatage $horodatage) : void
    {
        $this->horodatages->add($horodatage);
    }

    /** Pas besoin de remove car les horodatages vont "cascader" le relation */
}