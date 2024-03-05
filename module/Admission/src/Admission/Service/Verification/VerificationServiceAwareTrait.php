<?php

namespace Admission\Service\Verification;

use Admission\Service\Verification\VerificationService;

trait VerificationServiceAwareTrait
{
    /**
     * @var VerificationService
     */
    protected VerificationService $verificationService;

    /**
     * @param VerificationService $verificationService
     */
    public function setVerificationService(VerificationService $verificationService): void
    {
        $this->verificationService = $verificationService;
    }

    /**
     * @return VerificationService
     */
    public function getVerificationService(): VerificationService
    {
        return $this->verificationService;
    }
}