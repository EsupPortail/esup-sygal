<?php

namespace Doctorant\Controller\Plugin;

use Doctorant\Entity\Db\Doctorant;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlDoctorant extends UrlPlugin
{
    public function modifierEmailContactUrl(Doctorant $doctorant, bool $force = false): string
    {
        return $this->fromRoute('doctorant/modifier-email-contact',
            ['doctorant' => $doctorant->getId()], ['query' => ['force' => $force]], true);
    }

    public function modifierEmailContactConsentUrl(Doctorant $doctorant, ?string $redirect = null): string
    {
        return $this->fromRoute('doctorant/modifier-email-contact-consent',
            ['doctorant' => $doctorant->getId()], ['query' => ['redirect' => $redirect]], true);
    }
}