<?php

namespace Soutenance\Form\DateLieu;

trait DateLieuFormAwareTrait{

    /** @var DateLieuForm */
    private $dateLieuForm;

    /**
     * @return DateLieuForm
     */
    public function getDateLieuForm()
    {
        return $this->dateLieuForm;
    }

    /**
     * @param DateLieuForm $dateLieuForm
     * @return DateLieuForm
     */
    public function setDateLieuForm($dateLieuForm)
    {
        $this->dateLieuForm = $dateLieuForm;
        return $this->dateLieuForm;
    }
}