<?php

namespace Validation\Renderer;

use Application\Renderer\AbtractRendererAdapter;
use Validation\Entity\Db\ValidationHDR;

class ValidationHDRRendererAdapter extends AbtractRendererAdapter
{
    private ValidationHDR $validation;

    public function __construct(ValidationHDR $validation)
    {
        $this->validation = $validation;
    }

    public function getAuteurToString(): string
    {
        return $this->validation->getAuteurToString();
    }
    public function getDateToString(): string
    {
        return $this->validation->getDateToString();
    }
}