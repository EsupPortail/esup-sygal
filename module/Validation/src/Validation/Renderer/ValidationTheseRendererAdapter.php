<?php

namespace Validation\Renderer;

use Application\Renderer\AbtractRendererAdapter;
use Validation\Entity\Db\ValidationThese;

class ValidationTheseRendererAdapter extends AbtractRendererAdapter
{
    private ValidationThese $validation;

    public function __construct(ValidationThese $validation)
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