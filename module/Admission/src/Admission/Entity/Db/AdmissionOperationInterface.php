<?php

namespace Admission\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;

interface AdmissionOperationInterface extends HistoriqueAwareInterface
{
    public function getId();
    public function getAdmission(): Admission;
    public function getTypeToString(): string;
    public function getValeurBool(): bool;
    public function matches(AdmissionOperationInterface $otherOperation): bool;
    public function __toString(): string;
}