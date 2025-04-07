<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class InscriptionPrivileges extends Privileges {
    public const INSCRIPTION_INDEX      = 'formation_inscription-index';
    public const INSCRIPTION_AFFICHER   = 'formation_inscription-afficher';
    public const INSCRIPTION_AJOUTER    = 'formation_inscription-ajouter';
    public const INSCRIPTION_MODIFIER   = 'formation_inscription-gerer_liste';
    public const INSCRIPTION_HISTORISER = 'formation_inscription-historiser';
    public const INSCRIPTION_SUPPRIMER  = 'formation_inscription-supprimer';
    public const INSCRIPTION_CONVOCATION = 'formation_inscription-generer_convocation';
    public const INSCRIPTION_ATTESTATION = 'formation_inscription-generer_attestation';
    public const INSCRIPTION_SURSIS = 'formation_inscription-accorder_sursis';
}