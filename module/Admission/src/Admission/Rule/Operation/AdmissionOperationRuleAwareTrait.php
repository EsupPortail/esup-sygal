<?php

namespace Admission\Rule\Operation;

trait AdmissionOperationRuleAwareTrait
{
    protected AdmissionOperationRule $admissionOperationRule;

    /**
     * @param AdmissionOperationRule $admissionOperationRule
     */
    public function setAdmissionOperationRule(AdmissionOperationRule $admissionOperationRule): void
    {
        $this->admissionOperationRule = $admissionOperationRule;
    }
}