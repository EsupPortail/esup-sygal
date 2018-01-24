<?php

namespace Application\Service;

trait MailerServiceAwareTrait
{
    /**
     * @var MailerService
     */
    protected $mailerService;

    /**
     * @param MailerService $mailerService
     */
    public function setMailerService(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }
}