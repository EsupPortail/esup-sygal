<?php

namespace Formation\Entity\Db\Interfaces;

use Application\Entity\Db\Structure;

interface HasTypeInterface {

    const TYPE_SPECIFIQUE       = 'S';
    const TYPE_TRANSVERSALE     = 'T';

    public function getType() : ?string;
    public function setType(?string $type) : HasTypeInterface;

    public function getTypeStructure() : ?Structure;
    public function setTypeStructure(?Structure $typeStructure) : HasTypeInterface;
}