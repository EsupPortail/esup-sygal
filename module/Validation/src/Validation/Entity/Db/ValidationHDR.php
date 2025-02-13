<?php

namespace Validation\Entity\Db;

use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;

class ValidationHDR extends AbstractValidationEntity
{
    private ?HDR $hdr = null;

    public function __construct(Validation $validation, HDR|null $hdr = null, Individu|null $individu = null)
    {
        parent::__construct($validation, $individu);

        $this->setHDR($hdr);
    }

    public function setHDR(HDR $hdr = null): static
    {
        $this->hdr = $hdr;

        return $this;
    }

    public function getHDR(): HDR
    {
        return $this->hdr;
    }

    public function getResourceId(): string
    {
        return 'ValidationHDR';
    }
}
