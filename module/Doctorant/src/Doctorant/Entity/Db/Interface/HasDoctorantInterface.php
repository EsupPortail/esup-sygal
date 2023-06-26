<?php

namespace Doctorant\Entity\Db\Interface;

use Doctorant\Entity\Db\Doctorant;

interface HasDoctorantInterface
{

    public function setDoctorant(?Doctorant $doctorant): void;
    public function getDoctorant(): ?Doctorant;

}