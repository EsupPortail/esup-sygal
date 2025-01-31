<?php

namespace Doctorant\Entity\Db;

interface HasDoctorantInterface
{

    public function setDoctorant(?Doctorant $doctorant): void;
    public function getDoctorant(): ?Doctorant;

}