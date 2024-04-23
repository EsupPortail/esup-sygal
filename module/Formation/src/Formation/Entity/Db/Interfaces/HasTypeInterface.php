<?php

namespace Formation\Entity\Db\Interfaces;

use Structure\Entity\Db\Structure;

interface HasTypeInterface {

    const TYPE_SPECIFIQUE       = 'S';
    const TYPE_TRANSVERSALE     = 'T';

    const TYPES = [
        self::TYPE_SPECIFIQUE => "Spécifique",
        self::TYPE_TRANSVERSALE => "Transversale",
    ];

    public function getType() : ?string;
    public function setType(?string $type) : HasTypeInterface;

    public function getTypeStructure() : ?Structure;
    public function setTypeStructure(?Structure $typeStructure) : HasTypeInterface;
}