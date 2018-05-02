<?php

namespace Notification\Service\Mailer;

interface MailerServiceAwareInterface
{
    public function setMailerService(MailerService $mailerService);
}