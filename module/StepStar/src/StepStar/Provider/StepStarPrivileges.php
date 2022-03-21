<?php

namespace StepStar\Provider;

use UnicaenAuth\Provider\Privilege\Privileges;

class StepStarPrivileges extends Privileges
{
    const LOG_LISTER = 'step-star-log-lister';
    const LOG_CONSULTER = 'step-star-log-consulter';

    const TEF_TELECHARGER = 'step-star-tef-telecharger';
}