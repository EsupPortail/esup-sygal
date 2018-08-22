<?php

namespace Application\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class SoutenancePrivilege extends Privileges
{
    const SOUTENANCE_CONSULTATION                                = 'soutenance-consultation';
    const SOUTENANCE_MODIFICATION_DATE_LIEU                      = 'soutenance-modification-date-lieu';
    const SOUTENANCE_MODIFICATION_MEMBRE_JURY                    = 'soutenance-modification-membre-jury';
    const SOUTENANCE_VALIDATION_JURY                             = 'soutenance-validation-soutenance';
    const SOUTENANCE_MODIFICATION_DATE_RENDU_RAPPORT             = 'soutenance-modification-date-rendu-rapport';
    const SOUTENANCE_MODIFICATION_PERSOPASS                      = 'soutenance-modification-persopass';
    const SOUTENANCE_NOTIFICATION_DEMANDE_EXPERTISE              = 'soutenance-notification-demande-expertise';
    const SOUTENANCE_VALIDATION_DEMANDE_EXPERTISE                = 'soutenance-validation-demande-expertise';



}