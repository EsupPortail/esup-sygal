<?php

namespace These\Service\TheseAnneeUniv;

class TheseAnneeUnivServiceFactory
{
    public function __invoke()
    {
        return new TheseAnneeUnivService();
    }
}