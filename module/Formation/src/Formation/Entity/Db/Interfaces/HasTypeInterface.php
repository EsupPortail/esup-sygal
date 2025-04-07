<?php

namespace Formation\Entity\Db\Interfaces;

use Structure\Entity\Db\Structure;

interface HasTypeInterface {

    public const TYPE_SPECIFIQUE = 'S';
    public const TYPE_TRANSVERSALE = 'T';

    public const TYPES = [
        self::TYPE_SPECIFIQUE => "Spécifique",
        self::TYPE_TRANSVERSALE => "Transversale",
    ];

    public function getType() : ?string;
    public function setType(?string $type) : HasTypeInterface;

    public function getTypeStructure() : ?Structure;
    public function setTypeStructure(?Structure $typeStructure) : HasTypeInterface;
}