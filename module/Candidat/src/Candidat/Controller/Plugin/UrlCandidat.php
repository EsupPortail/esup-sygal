<?php

namespace Candidat\Controller\Plugin;

use Candidat\Entity\Db\Candidat;
use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;

class UrlCandidat extends UrlPlugin
{
    public function modifierEmailContactUrl(Candidat $candidat, bool $force = false): string
    {
        return $this->fromRoute('candidat/modifier-email-contact',
            ['candidat' => $candidat->getId()], ['query' => ['force' => $force]], true);
    }

    public function modifierEmailContactConsentUrl(Candidat $candidat, ?string $redirect = null): string
    {
        return $this->fromRoute('candidat/modifier-email-contact-consent',
            ['candidat' => $candidat->getId()], ['query' => ['redirect' => $redirect]], true);
    }
}