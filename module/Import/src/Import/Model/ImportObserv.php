<?php

namespace Import\Model;

class ImportObserv extends \UnicaenDbImport\Entity\Db\ImportObserv
{
    public const CODE_RESULTAT_PASSE_A_ADMIS = 'RESULTAT_PASSE_A_ADMIS';
    public const CODE_CORRECTION_PASSE_A_FACULTATIVE = 'CORRECTION_PASSE_A_FACULTATIVE';
    public const CODE_CORRECTION_PASSE_A_OBLIGATOIRE = 'CORRECTION_PASSE_A_OBLIGATOIRE';

    public const CODES = [
        self::CODE_RESULTAT_PASSE_A_ADMIS,
        self::CODE_CORRECTION_PASSE_A_FACULTATIVE,
        self::CODE_CORRECTION_PASSE_A_OBLIGATOIRE,
    ];
}
