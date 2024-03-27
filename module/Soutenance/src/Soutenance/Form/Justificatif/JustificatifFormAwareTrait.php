<?php

namespace Soutenance\Form\Justificatif;

trait JustificatifFormAwareTrait
{
    protected JustificatifForm $justificatifForm;

    public function getJustificatifForm(): JustificatifForm
    {
        return $this->justificatifForm;
    }

    public function setJustificatifForm(JustificatifForm $justificatifForm): void
    {
        $this->justificatifForm = $justificatifForm;
    }
}