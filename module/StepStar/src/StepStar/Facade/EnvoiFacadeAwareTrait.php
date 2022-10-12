<?php

namespace StepStar\Facade;

trait EnvoiFacadeAwareTrait
{
    protected EnvoiFacade $envoiFacade;

    public function setEnvoiFacade(EnvoiFacade $envoiFacade): void
    {
        $this->envoiFacade = $envoiFacade;
    }
}