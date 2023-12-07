<?php

namespace Substitution;

class Constants
{
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
}