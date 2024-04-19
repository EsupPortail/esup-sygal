<?php

namespace Application\Service\ListeDiffusion\Url;

use Application\Entity\Db\ListeDiffusion;

class UrlService extends \Application\Service\Url\UrlService
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function generateMemberIncludeUrl(ListeDiffusion $liste): string
    {
        return $this->fromRoute('liste-diffusion/liste/generate-member-include',
            ['adresse' => $liste->getAdresse(), 'token' => $this->token], ['force_canonical' => true]);
    }

    public function generateOwnerIncludeUrl(ListeDiffusion $liste): string
    {
        return $this->fromRoute('liste-diffusion/liste/generate-owner-include',
            ['adresse' => $liste->getAdresse(), 'token' => $this->token], ['force_canonical' => true]);
    }
}