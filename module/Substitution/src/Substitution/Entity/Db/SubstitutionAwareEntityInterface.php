<?php

namespace Substitution\Entity\Db;

use Doctrine\Common\Collections\Collection;

/**
 * Interface spécifiant les enregistrements supportés par le moteur de substitutions.
 */
interface SubstitutionAwareEntityInterface
{
    /**
     * Retourne l'id de cet enregistrement.
     */
    public function getId();

    /**
     *
     */
    public function extractAttributeValues(array $attributes): array;

    /**
     * Retourne l'éventuel NPD forcé de cet enregistrement.
     */
    public function getNpdForce(): ?string;

    /**
     * Retourne les éventuels enregistrements substitués par cet enregistrement.
     *
     * NB : attention à faire la jointure en cas de parcours de plusieurs enregistrements.
     */
    public function getSubstitues(): Collection;

    /**
     * Indique si cet enregistrement est un substituant, autrement dit s'il possède des substitués.
     *
     * NB : attention à faire la jointure en cas de parcours de plusieurs enregistrements.
     */
    public function estSubstituant(): bool;

    /**
     * Indique si la mise à jour automatique des attributs de ce substituant par le moteur de substitutions est activée.
     */
    public function estSubstituantModifiable(): bool;

    /**
     * Spécifie si la mise à jour automatique des attributs de ce substituant par le moteur de substitutions est activée.
     *
     * Si la mise à jour automatique est désactivée (`false`), la modification du substituant par l'utilisateur
     * est possible.
     */
    public function setEstSubstituantModifiable(bool $value = true): void;
}