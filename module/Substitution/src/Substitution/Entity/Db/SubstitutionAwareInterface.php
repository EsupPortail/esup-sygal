<?php

namespace Substitution\Entity\Db;

interface SubstitutionAwareInterface
{
    public function updateEnabled(): bool;
}