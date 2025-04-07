<?php

namespace These\Search\These;

use Application\Search\Filter\TextCriteriaSearchFilter;

class TheseTextSearchFilter extends TextCriteriaSearchFilter
{
    protected string $name = 'text';
    protected string $label = "Recherche de texte";

    public const CRITERIA_titre = 'titre';
    public const CRITERIA_numero_doctorant = 'doctorant-numero';
    public const CRITERIA_nom_doctorant = 'doctorant-nom';
    public const CRITERIA_prenom_doctorant = 'doctorant-prenom';
    public const CRITERIA_nom_directeur = 'directeur-nom';
    public const CRITERIA_code_ed = 'code-ed';
    public const CRITERIA_code_ur = 'code_ur';

    /**
     * ATTENTION : les identifiants (clés) doivent être identiques à ceux utilisés dans
     *             le script de la vue matérialisée MV_RECHERCHE_THESE sollicitée pour la recherche.
     */
    public const CRITERIA = [
        self::CRITERIA_titre => "Titre de la thèse",
        self::CRITERIA_numero_doctorant => "Numéro étudiant du doctorant",
        self::CRITERIA_nom_doctorant => "Nom d'usage ou patronymique du doctorant",
        self::CRITERIA_prenom_doctorant => "Prénom du doctorant",
        self::CRITERIA_nom_directeur => "Nom d'usage ou patronymique du directeur ou co-directeur de thèse",
        self::CRITERIA_code_ed => "Code national de l'école doctorale concernée (ex: 181)",
        self::CRITERIA_code_ur => "Unité de recherche concernée (ex: umr6211)",
    ];

    /**
     * Critères possibles sur lesquels faire porter la recherche sur texte libre.
     */
    protected array $availableCriteria = self::CRITERIA;
}
