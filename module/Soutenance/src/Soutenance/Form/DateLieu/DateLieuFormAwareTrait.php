<?php

namespace Soutenance\Form\DateLieu;

trait DateLieuFormAwareTrait
{
    private DateLieuForm $dateLieuForm;

    /**
     * @return DateLieuForm
     */
    public function getDateLieuForm(): DateLieuForm
    {
        return $this->dateLieuForm;
    }

    public function setDateLieuForm(DateLieuForm $dateLieuForm): void
    {
        $this->dateLieuForm = $dateLieuForm;
    }
}