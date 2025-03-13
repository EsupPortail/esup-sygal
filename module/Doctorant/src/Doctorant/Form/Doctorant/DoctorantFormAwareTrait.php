<?php

namespace Doctorant\Form\Doctorant;

trait DoctorantFormAwareTrait
{
    protected DoctorantForm $doctorantForm;

    public function setDoctorantForm(DoctorantForm $doctorantForm): void
    {
        $this->doctorantForm = $doctorantForm;
    }
}