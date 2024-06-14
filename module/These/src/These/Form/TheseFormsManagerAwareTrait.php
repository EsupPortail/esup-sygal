<?php

namespace These\Form;

trait TheseFormsManagerAwareTrait
{
    protected TheseFormsManager $theseFormsManager;

    public function setTheseFormsManager(TheseFormsManager $theseFormsManager): void
    {
        $this->theseFormsManager = $theseFormsManager;
    }
}