<?php

namespace Soutenance\Form\DateLieu;

trait DateLieuFormAwareTrait
{
    private DateLieuForm $dateLieuForm;

    public function setDateLieuForm(DateLieuForm $dateLieuForm): void
    {
        $this->dateLieuForm = $dateLieuForm;
    }
}