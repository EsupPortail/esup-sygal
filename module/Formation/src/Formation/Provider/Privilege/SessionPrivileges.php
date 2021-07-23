<?php

namespace Formation\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class SessionPrivileges extends Privileges {

    const SESSION_INDEX      = 'formation_session-index';
    const SESSION_AFFICHER   = 'formation_session-afficher';
    const SESSION_AJOUTER    = 'formation_session-ajouter';
    const SESSION_MODIFIER   = 'formation_session-modifier';
    const SESSION_HISTORISER = 'formation_session-historiser';
    const SESSION_SUPPRIMER  = 'formation_session-supprimer';
    const SESSION_INSCRIPTION  = 'formation_session-gerer_inscription';
}