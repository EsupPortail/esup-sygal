<?php

namespace Doctorant\Controller\Plugin;

use Application\Entity\Db\These;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlDoctorant extends UrlPlugin
{
    public function modifierPersopassUrl(These $these)
    {
        return $this->fromRoute('doctorant/modifier-email-contact',
            ['doctorant' => $these->getDoctorant()->getId(), 'back' => 0],[], true
        );
    }
}