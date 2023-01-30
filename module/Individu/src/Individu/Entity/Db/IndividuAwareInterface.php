<?php

namespace Individu\Entity\Db;

interface IndividuAwareInterface
{
    public function getIndividu(): ?Individu;
    public function setIndividu(?Individu $individu = null);
}