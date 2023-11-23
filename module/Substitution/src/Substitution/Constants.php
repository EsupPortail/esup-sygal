<?php

namespace Substitution;

class Constants
{
    const USE_TABLE_PREFIX = FALSE;

    const TYPE_structure = 'structure';
    const TYPE_etablissement = 'etablissement';
    const TYPE_ecole_doct = 'ecole_doct';
    const TYPE_unite_rech = 'unite_rech';
    const TYPE_individu = 'individu';
    const TYPE_doctorant = 'doctorant';

    const TYPES = [
        self::TYPE_structure,
        self::TYPE_etablissement,
        self::TYPE_ecole_doct,
        self::TYPE_unite_rech,
        self::TYPE_individu,
        self::TYPE_doctorant,
    ];

    const TYPES_REGEXP_CONSTRAINT = '(structure|etablissement|ecole_doct|unite_rech|individu|doctorant)';

    const MSG_SUBSTITUE_INTROUVABLE =
        "La substitution de cet enregistrement ayant eu lieu dès l'apparition de ce dernier lors de la synchro, il n'existe pas dans la table finale 'XXX' mais seulement dans la table intermédiaire 'PRE_XXX'.";
}