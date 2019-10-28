<?php

namespace Soutenance\Form\ActeurSimule;

trait ActeurSimuleFormAwareTrait {

    /** @var ActeurSimuleForm */
    private $acteurSimuleForm;

    /**
     * @return ActeurSimuleForm
     */
    public function getActeurSimuleForm()
    {
        return $this->acteurSimuleForm;
    }

    /**
     * @param ActeurSimuleForm $acteurSimuleForm
     * @return ActeurSimuleForm
     */
    public function setActeurSimuleForm($acteurSimuleForm)
    {
        $this->acteurSimuleForm = $acteurSimuleForm;
        return $this->acteurSimuleForm;
    }


}