<?php

namespace Application\Service\Email;

trait EmailServiceAwareTrait
{
    /**
     * @var EmailService
     */
    protected EmailService $emailService;

    /**
     * @param EmailService $emailService
     */
    public function setEmailService(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
}