<?php

namespace Substitution\Entity\Db;

use Doctrine\Common\Collections\Collection;

interface SubstitutionAwareInterface
{
    /**
     * Retourne les éventuels enregistrements substitués par celui-ci.
     *
     * NB : attention à faire la jointure en cas de parcours de plusieurs enregistrements.
     */
    public function getSubstitues(): Collection;

    /**
     * Indique si cet enregistrement est un substituant.
     */
    public function estSubstituant(): bool;

    /**
     * Indique si ce substituant peut être modifié manuellement.
     *
     * Si c'est le cas, cela désactive la mise à jour automatique de ses attributs par le mécanisme de recherche
     * de substitutions.
     */
    public function estSubstituantModifiable(): bool;
}