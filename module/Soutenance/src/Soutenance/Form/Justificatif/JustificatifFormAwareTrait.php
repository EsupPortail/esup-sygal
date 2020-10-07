<?php

namespace Soutenance\Form\Justificatif;

trait JustificatifFormAwareTrait {

    /** @var JustificatifForm */
    private $justificatifForm;

    /**
     * @return JustificatifForm
     */
    public function getJustificatifForm()
    {
        return $this->justificatifForm;
    }

    /**
     * @param JustificatifForm $justificatifForm
     * @return JustificatifForm
     */
    public function setJustificatifForm($justificatifForm)
    {
        $this->justificatifForm = $justificatifForm;
        return $this->justificatifForm;
    }

}