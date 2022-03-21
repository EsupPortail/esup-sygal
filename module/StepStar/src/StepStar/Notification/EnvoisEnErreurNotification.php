<?php

namespace StepStar\Notification;

use Notification\Notification;

class EnvoisEnErreurNotification extends Notification
{
    /**
     * @var \StepStar\Entity\Db\Log[]
     */
    private array $logs;

    /**
     * @param \StepStar\Entity\Db\Log[] $logs
     */
    public function setLogs(array $logs): void
    {
        $this->logs = $logs;
    }

    public function prepare()
    {
        parent::prepare();

        $this->setTemplateVariables([
            'logs' => $this->logs,
        ]);
    }
}