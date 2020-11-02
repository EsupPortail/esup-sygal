<?php

namespace Soutenance\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class PresoutenancePrivileges extends Privileges
{
    const PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU = 'soutenance-association-membre-individu';
    const PRESOUTENANCE_DATE_RETOUR_MODIFICATION    = 'soutenance-modification-date-rapport';
    const PRESOUTENANCE_PRESOUTENANCE_VISUALISATION = 'soutenance-presoutenance-visualisation';
}