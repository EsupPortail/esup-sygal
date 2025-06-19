<?php

namespace Formation\Service\Parametre;

use Formation\Provider\Parametre\FormationParametres;
use UnicaenParametre\Service\Parametre\ParametreServiceAwareTrait;

class ParametreService
{
    use ParametreServiceAwareTrait;

    public function getDelaiEnquete() : bool|int|string|null
    {
        return $this->parametreService->getValeurForParametre(FormationParametres::CATEGORIE, FormationParametres::DELAI_ENQUETE);
    }
}