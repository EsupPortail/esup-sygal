<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface commune à tous les filtres de recherche.
 */
interface SearchFilterInterface
{
    /**
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams): void;

    /**
     * Retourne `true` si ce filtre peut être appliqué au query builder, en fonction de sa valeur courante.
     */
    public function canApplyToQueryBuilder(): bool;

    /**
     * Applique au query builder spécifié le·s condition·s correspondant à la valeur de ce filtre.
     * NB : cette méthode n'est pas appelée si {@see canApplyToQueryBuilder()} retourne `false`.
     */
    public function applyToQueryBuilder(QueryBuilder $qb): void;

    /**
     * Applique au query builder spécifié les conditions par défaut imposées par ce filtre, le cas échéant.
     * NB : cette méthode est appelée systématiquement.
     */
    public function applyDefaultsToQueryBuilder(QueryBuilder $qb): void;

    /**
     * Retourne le petit nom de ce filtre (unique au sein d'un ensemble de filtres).
     */
    public function getName(): string;

    /**
     * Spécifie le petit nom de ce filtre (unique au sein d'un ensemble de filtres).
     */
    public function setName(string $name);

    /**
     * Retourne le label/intitulé de ce filtre, visible sur le formulaire de filtrage.
     */
    public function getLabel(): string;

    /**
     * Spécifie le label/intitulé de ce filtre, visible sur le formulaire de filtrage.
     */
    public function setLabel(string $label);

    /**
     * Retourne la valeur courante de ce filtre.
     */
    public function getValue();

    /**
     * Spécifie la valeur courante de ce filtre.
     */
    public function setValue($value);

    /**
     * Retourne la liste des attributs de ce filtre, ex : ['class' => 'selectpicker']
     */
    public function getAttributes(): array;

    /**
     * Spécifie la liste des attributs de ce filtre, ex : ['class' => 'selectpicker'],
     * avec/sans écrasement des attributs existants.
     */
    public function setAttributes(array $attributes, bool $overwrite = false);
}
