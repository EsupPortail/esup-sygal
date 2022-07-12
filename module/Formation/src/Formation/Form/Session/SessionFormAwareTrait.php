<?php

namespace Formation\Form\Session;

trait SessionFormAwareTrait {

    /** @var SessionForm */
    private $moduleForm;

    /**
     * @return SessionForm
     */
    public function getSessionForm(): SessionForm
    {
        return $this->moduleForm;
    }

    /**
     * @param SessionForm $moduleForm
     * @return SessionForm
     */
    public function setSessionForm(SessionForm $moduleForm): SessionForm
    {
        $this->moduleForm = $moduleForm;
        return $this->moduleForm;
    }
}