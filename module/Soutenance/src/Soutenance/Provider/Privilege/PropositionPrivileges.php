<?php

namespace Soutenance\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class PropositionPrivileges extends Privileges
{
    // PROPOSITION ET VALIDATION DE LA PROPOSITION ---------------------------------------------------------------------
    public const PROPOSITION_VISUALISER = 'soutenance-proposition-visualisation';
    public const PROPOSITION_MODIFIER = 'soutenance-proposition-modification';
    public const PROPOSITION_MODIFIER_GESTION = 'soutenance-proposition-modification_gestion';
    public const PROPOSITION_VALIDER_ACTEUR = 'soutenance-proposition-validation-acteur';
    public const PROPOSITION_VALIDER_ED = 'soutenance-proposition-validation-ed';
    public const PROPOSITION_VALIDER_UR = 'soutenance-proposition-validation-ur';
    public const PROPOSITION_VALIDER_BDD = 'soutenance-proposition-validation-bdd';
    public const PROPOSITION_PRESIDENCE = 'soutenance-proposition-presidence';
    public const PROPOSITION_SURSIS = 'soutenance-proposition-sursis';
    public const PROPOSITION_DECLARATION_HONNEUR_VALIDER = 'soutenance-declaration-honneur-valider';
    public const PROPOSITION_DECLARATION_HONNEUR_REVOQUER = 'soutenance-declaration-honneur-revoquer';
    public const PROPOSITION_REVOQUER_STRUCTURE = 'soutenance-proposition-revoquer-structure';
    public const PROPOSITION_SUPPRIMER_INFORMATIONS = 'soutenance-proposition-supprimer';
}