<?php

namespace Admission\Rule\Operation\Notification;

trait OperationAttendueNotificationRuleAwareTrait
{
    protected OperationAttendueNotificationRule $admissionOperationAttendueNotificationRule;

    public function setAdmissionOperationAttendueNotificationRule(OperationAttendueNotificationRule $admissionOperationAttendueNotificationRule): void
    {
        $this->admissionOperationAttendueNotificationRule = $admissionOperationAttendueNotificationRule;
    }
}