<?php

namespace Notification\Service\Mailer;

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