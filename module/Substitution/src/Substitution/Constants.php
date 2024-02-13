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

    const ALERTE_1_SEUL_SUBSTITUE = "Cette substitution ne met en jeu qu'un seul enregistrement substitué,
        ce qui n'est pas une situation normale puisque le moteur de substitutions automatiques se base sur la recherche
        de doublons. Donc il s'agit sans doute d'une substitution de 2 enregistrements qui a été modifiée manuellement
        (retrait d'un des 2 substitués par le forçage de son NPD) et dont le substituant n'a pu être supprimé comme prévu
        en raison d'une contrainte d'intégrité (référencement dans une autre table), alors qu'il aurait dû l'être du fait
        qu'il ne restait plus qu'un seul substitué.";
}