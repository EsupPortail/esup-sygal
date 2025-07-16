<?php

namespace Depot\Rule\Result;

use DateTime;

/**
 * Résultat d'interrogation de la règle métier concernant la nécessité ou non de remise d'un exemplaire papier
 * de la thèse.
 */
class RemiseExemplairePapierRequiredResult
{
    /** @var \DateTime Date d'observation utilisée */
    public DateTime $dateObservation;

    /** @var string|null Description éventuelle de la date d'observation utilisée */
    public ?string $dateObservationDesc = null;

    /** @var bool Indiquae si la remise d'un exemplaire papier est requise à la date d'observation */
    public bool $isRequired;

    /** @var bool Indiquae si la remise d'un exemplaire papier est requise à la date d'aujourd'hui */
    public bool $isRequiredNow;

    /** @var string|null Un message informatif éventuel */
    public ?string $message = null;
}