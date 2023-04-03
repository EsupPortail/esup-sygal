<?php

namespace UnicaenIdref;

use UnicaenIdref\Domain\AbstractFiltre;
use UnicaenIdref\Domain\Index1;
use UnicaenIdref\Domain\Index2;
use UnicaenIdref\Domain\Index3;

class ParamsFactory
{
    static public function new(): Params
    {
        return new Params();
    }

    public static function fromParamValues(
        string  $fromApp,
        Index1  $index1,
        ?Index2 $index2 = null,
        ?Index3 $index3 = null,
        ?AbstractFiltre $filtre1 = null,
        ?AbstractFiltre $filtre2 = null,
        ?AbstractFiltre $filtre3 = null,
        ?AbstractFiltre $filtre4 = null,
        ?string $zones = null): Params
    {
        $inst = new Params();

        $inst->setFromApp($fromApp);
        $inst->setIndex1($index1);
        $inst->setIndex2($index2);
        $inst->setIndex3($index3);
        $inst->setFiltre1($filtre1);
        $inst->setFiltre2($filtre2);
        $inst->setFiltre3($filtre3);
        $inst->setFiltre4($filtre4);
        $inst->setZones($zones);

        return $inst;
    }
}