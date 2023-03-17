<?php

namespace Formation\Provider\Privilege;

use UnicaenPrivilege\Provider\Privilege\Privileges;

class EnquetePrivileges extends Privileges
{
    const ENQUETE_QUESTION_AFFICHER   = 'formation_enquete-question_afficher';
    const ENQUETE_QUESTION_AJOUTER    = 'formation_enquete-question_ajouter';
    const ENQUETE_QUESTION_MODIFIER   = 'formation_enquete-question_modifier';
    const ENQUETE_QUESTION_HISTORISER = 'formation_enquete-question_historiser';
    const ENQUETE_QUESTION_SUPPRIMER  = 'formation_enquete-question_supprimer';

    const ENQUETE_REPONSE_REPONDRE    = 'formation_enquete-reponse_repondre';
    const ENQUETE_REPONSE_RESULTAT    = 'formation_enquete-reponse_resultat';
}