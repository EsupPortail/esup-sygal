<?php

namespace Application\Service;

trait MailConfirmationServiceAwareTrait
{
    /**
     * @var MailConfirmationService
     */
    private $mailConfirmationService;

    /**
     * @param MailConfirmationService $service
     * @return self
     */
    public function setMailConfirmationService(MailConfirmationService $service)
    {
        $this->mailConfirmationService = $service;

        return $this;
    }
}