<?php

namespace Soutenance\Provider\Privilege;

use UnicaenAuth\Provider\Privilege\Privileges;

class PropositionPrivileges extends Privileges
{
    // PROPOSITION ET VALIDATION DE LA PROPOSITION ---------------------------------------------------------------------
    const PROPOSITION_VISUALISER                      = 'soutenance-proposition-visualisation';
    const PROPOSITION_MODIFIER                        = 'soutenance-proposition-modification';
    const PROPOSITION_MODIFIER_GESTION                = 'soutenance-proposition-modification_gestion';
    const PROPOSITION_VALIDER_ACTEUR                  = 'soutenance-proposition-validation-acteur';
    const PROPOSITION_VALIDER_ED                      = 'soutenance-proposition-validation-ed';
    const PROPOSITION_VALIDER_UR                      = 'soutenance-proposition-validation-ur';
    const PROPOSITION_VALIDER_BDD                     = 'soutenance-proposition-validation-bdd';
    const PROPOSITION_PRESIDENCE                      = 'soutenance-proposition-presidence';
    const PROPOSITION_SURSIS                          = 'soutenance-proposition-sursis';
    const PROPOSITION_DECLARATION_HONNEUR_VISUALISER  = 'soutenance-declaration-honneur-visualiser';
    const PROPOSITION_DECLARATION_HONNEUR_VALIDER     = 'soutenance-declaration-honneur-valider';
}