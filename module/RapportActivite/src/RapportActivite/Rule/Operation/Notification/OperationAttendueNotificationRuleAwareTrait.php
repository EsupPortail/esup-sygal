<?php

namespace RapportActivite\Rule\Operation\Notification;

trait OperationAttendueNotificationRuleAwareTrait
{
    protected OperationAttendueNotificationRule $rapportActiviteOperationAttendueNotificationRule;

    public function setRapportActiviteOperationAttendueNotificationRule(OperationAttendueNotificationRule $rapportActiviteOperationAttendueNotificationRule): void
    {
        $this->rapportActiviteOperationAttendueNotificationRule = $rapportActiviteOperationAttendueNotificationRule;
    }
}