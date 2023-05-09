<?php

namespace Application\Entity;

interface AnneeUnivInterface
{
    public function getPremiereAnnee(): int;

    public function getAnneeUnivToString(): string;
}