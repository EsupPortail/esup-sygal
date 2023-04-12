<?php

namespace RapportActivite\Entity;

use RapportActivite\Entity\Db\RapportActivite;
use UnicaenApp\Entity\HistoriqueAwareInterface;

interface RapportActiviteOperationInterface extends HistoriqueAwareInterface
{
    public function getId();
    public function getRapportActivite(): RapportActivite;
    public function getTypeToString(): string;
    public function getValeurBool(): bool;
    public function matches(RapportActiviteOperationInterface $otherOperation): bool;
    public function __toString(): string;
}