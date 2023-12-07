<?php

namespace Substitution\Service\Substitution;

use Doctrine\DBAL\Result;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;

interface SpecificSubstitutionServiceInterface
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutions(): int;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutions(?int $limit = null): Result;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitution(int $substituantId): Result;

    /**
     * Recherche d'enregistrements pouvant être ajoutés manuellement à une substitution.
     */
    public function findSubstituablesByText(string $text, string $npd): array;

    /**
     * Ajoute un enregistrement (le futur substitué) à une substitution existante (spécifiée par le NPD).
     */
    public function addSubstitueToSubstitution(int $substituableId, string $npd): void;

    /**
     * Retrait d'un enregistrement (un substitué) d'une substitution existante (spécifiée par le NPD).
     */
    public function removeSubstitueFromSubstitution(int $substitueId, string $npd): void;

    /**
     * Recherche de l'entité (substituante ou substituée) par son id.
     */
    public function findOneEntityById(int $id): SubstitutionAwareEntityInterface;

    /**
     * Enregistre les modifications apportées à l'entité spécifiée.
     */
    public function saveEntity(SubstitutionAwareEntityInterface $entity): void;

    /**
     * Retourne la liste des colonnes participant au calcul du NPD.
     *
     * @return string[] Format : 'nom_colonne' => "Libellé".
     */
    public function getEntityNpdAttributes(): array;
}