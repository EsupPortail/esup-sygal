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
    // PROPOSITION ET VALIDATION DE LA PROPOSITION ---------------------------------------------------------------------
    const SOUTENANCE_PROPOSITION_VISUALISER                      = 'soutenance-proposition-visualisation';
    const SOUTENANCE_PROPOSITION_MODIFIER                        = 'soutenance-proposition-modification';
    const SOUTENANCE_PROPOSITION_VALIDER_ACTEUR                  = 'soutenance-proposition-validation-acteur';
    const SOUTENANCE_PROPOSITION_VALIDER_ED                      = 'soutenance-proposition-validation-ed';
    const SOUTENANCE_PROPOSITION_VALIDER_UR                      = 'soutenance-proposition-validation-ur';
    const SOUTENANCE_PROPOSITION_VALIDER_BDD                     = 'soutenance-proposition-validation-bdd';
    const SOUTENANCE_PROPOSITION_PRESIDENCE                      = 'soutenance-proposition-presidence';

    // PRESOUTENANCE ---------------------------------------------------------------------------------------------------
    const SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU                 = 'soutenance-association-membre-individu';
    const SOUTENANCE_DATE_RETOUR_MODIFICATION                    = 'soutenance-modification-date-rapport';
    const SOUTENANCE_PRESOUTENANCE_VISUALISATION                 = 'soutenance-presoutenance-visualisation';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER              = 'soutenance-engagement-impartialite-signer';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_ANNULER             = 'soutenance-engagement-impartialite-annuler';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER            = 'soutenance-engagement-impartialite-notifier';
    const SOUTENANCE_ENGAGEMENT_IMPARTIALITE_VISUALISER          = 'soutenance-engagement-impartialite-visualiser';
}