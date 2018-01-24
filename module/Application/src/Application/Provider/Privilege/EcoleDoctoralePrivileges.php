<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 */
class EcoleDoctoralePrivileges extends Privileges
{
    const ECOLE_DOCT_CONSULTATION = 'ecole-doctorale-consultation';
    const ECOLE_DOCT_MODIFICATION = 'ecole-doctorale-modification';
}