<?php

namespace Substitution\Service\Substitution;

use Doctrine\DBAL\Result;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;

interface SpecificSubstitutionServiceInterface
{
    /**
     * Retourne le type de substitution associé à ce service, cf. {@see \Substitution\Constants::TYPES}.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Création d'une substitution à partir de l'id d'un enregistrement substituable (1 parmi les doublons trouvés).
     *
     * @return Result Substitution créée
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createSubstitution(string $substituableId, string $npd): Result;

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
    public function findOneSubstitutionBySubstituant(int $substituantId): Result;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionBySubstitue(int $substitueId): Result;

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

    /**
     * Calcul le NPD d'un enregistrement spécifié par son id.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function computeEntityNpd(int $substituableId): string;
}