<?php

namespace Soutenance\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

/**
 * Liste des privilèges utilisables.
 *
 * @author UnicaenCode
 */
class SoutenancePrivileges extends Privileges
{
    const SOUTENANCE_CONSULTATION                                = 'soutenance-consultation';
    const SOUTENANCE_MODIFICATION_DATE_LIEU                      = 'soutenance-modification-date-lieu';
    const SOUTENANCE_MODIFICATION_MEMBRE_JURY                    = 'soutenance-modification-membre-jury';
    const SOUTENANCE_VALIDATION_JURY                             = 'soutenance-validation-soutenance';
    const SOUTENANCE_MODIFICATION_DATE_RENDU_RAPPORT             = 'soutenance-modification-date-rapport';
    const SOUTENANCE_MODIFICATION_PERSOPASS                      = 'soutenance-modification-persopass';
    const SOUTENANCE_NOTIFICATION_DEMANDE_EXPERTISE              = 'soutenance-notification-demande-expertise';
    const SOUTENANCE_VALIDATION_DEMANDE_EXPERTISE                = 'soutenance-validation-demande-expertise';

    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER              = 'soutenance-engagement-impartialite-signer';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_ANNULER             = 'soutenance-engagement-impartialite-annuler';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER            = 'soutenance-engagement-impartialite-notifier';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_VISUALISER          = 'soutenance-engagement-impartialite-visualiser';

    const SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU                 = 'soutenance-association-membre-individu';
    const SOUTENANCE_DATE_RETOUR_MODIFICATION                    = 'soutenance-date-retour-modification';
    const SOUTENANCE_PRESOUTENANCE_VISUALISATION                 = 'soutenance-presoutenance-visualisation';


}