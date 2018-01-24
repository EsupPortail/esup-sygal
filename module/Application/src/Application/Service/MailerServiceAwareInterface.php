<?php

namespace Application\Service;

interface MailerServiceAwareInterface
{
    public function setMailerService(MailerService $mailerService);
}