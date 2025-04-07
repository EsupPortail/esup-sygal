<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class EnquetePrivileges extends Privileges
{
    public const ENQUETE_QUESTION_AFFICHER = 'formation_enquete-question_afficher';
    public const ENQUETE_QUESTION_AJOUTER = 'formation_enquete-question_ajouter';
    public const ENQUETE_QUESTION_MODIFIER = 'formation_enquete-question_modifier';
    public const ENQUETE_QUESTION_HISTORISER = 'formation_enquete-question_historiser';
    public const ENQUETE_QUESTION_SUPPRIMER = 'formation_enquete-question_supprimer';

    public const ENQUETE_REPONSE_REPONDRE = 'formation_enquete-reponse_repondre';
    public const ENQUETE_REPONSE_RESULTAT = 'formation_enquete-reponse_resultat';
}