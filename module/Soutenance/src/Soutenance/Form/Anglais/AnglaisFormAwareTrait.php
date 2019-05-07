<?php

namespace Soutenance\Form\Anglais;

trait AnglaisFormAwareTrait {

    /** @var AnglaisForm $anglaisForm */
    private $anglaisForm;

    /**
     * @return AnglaisForm
     */
    public function getAnglaisForm()
    {
        return $this->anglaisForm;
    }

    /**
     * @param AnglaisForm $anglaisForm
     * @return AnglaisForm
     */
    public function setAnglaisForm($anglaisForm)
    {
        $this->anglaisForm = $anglaisForm;
        return $this->anglaisForm;
    }

}