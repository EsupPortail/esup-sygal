<?php

namespace Validation\Entity\Db;

use Individu\Entity\Db\Individu;
use These\Entity\Db\These;

class ValidationThese extends AbstractValidationEntity
{
    private ?These $these = null;

    public function __construct(Validation $validation, These|null $these = null, Individu|null $individu = null)
    {
        parent::__construct($validation, $individu);

        $this->setThese($these);
    }

    public function setThese(These $these = null): static
    {
        $this->these = $these;

        return $this;
    }

    public function getThese(): These
    {
        return $this->these;
    }

    public function getResourceId(): string
    {
        return 'ValidationThese';
    }
}
