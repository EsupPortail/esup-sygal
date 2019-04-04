<?php

namespace Application\Service\TheseAnneeUniv;

class TheseAnneeUnivServiceFactory
{
    public function __invoke()
    {
        return new TheseAnneeUnivService();
    }
}