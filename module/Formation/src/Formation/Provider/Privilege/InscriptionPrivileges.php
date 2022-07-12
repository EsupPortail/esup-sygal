<?php

namespace Formation\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class InscriptionPrivileges extends Privileges {
    const INSCRIPTION_INDEX      = 'formation_inscription-index';
    const INSCRIPTION_AFFICHER   = 'formation_inscription-afficher';
    const INSCRIPTION_AJOUTER    = 'formation_inscription-ajouter';
    const INSCRIPTION_MODIFIER   = 'formation_inscription-gerer_liste';
    const INSCRIPTION_HISTORISER = 'formation_inscription-historiser';
    const INSCRIPTION_SUPPRIMER  = 'formation_inscription-supprimer';
    const INSCRIPTION_CONVOCATION = 'formation_inscription-generer_convocation';
    const INSCRIPTION_ATTESTATION = 'formation_inscription-generer_attestation';
}