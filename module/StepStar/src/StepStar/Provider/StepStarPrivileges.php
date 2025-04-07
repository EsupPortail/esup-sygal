<?php

namespace StepStar\Provider;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class StepStarPrivileges extends Privileges
{
    public const LOG_LISTER = 'step-star-log-lister';
    public const LOG_CONSULTER = 'step-star-log-consulter';

    public const TEF_TELECHARGER = 'step-star-tef-telecharger';
}