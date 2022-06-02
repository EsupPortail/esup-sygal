<?php

namespace Application\Service\Email;

trait EmailTheseServiceAwareTrait
{
    /**
     * @var EmailTheseService
     */
    protected EmailTheseService $emailTheseService;

    /**
     * @param EmailTheseService $emailTheseService
     */
    public function setEmailTheseService(EmailTheseService $emailTheseService)
    {
        $this->emailTheseService = $emailTheseService;
    }
}