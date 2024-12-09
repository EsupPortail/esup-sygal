<?php

namespace Application\Renderer;

use Application\Entity\Db\Validation;

class ValidationRendererAdapter extends AbtractRendererAdapter
{
    private Validation $validation;

    public function __construct(Validation $validation)
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