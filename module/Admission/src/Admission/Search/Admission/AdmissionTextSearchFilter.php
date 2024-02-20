<?php

namespace Admission\Search\Admission;

use Application\Search\Filter\TextCriteriaSearchFilter;

/**
 *
 *
 * @author Unicaen
 */
class AdmissionTextSearchFilter extends TextCriteriaSearchFilter
{
    const NAME = 'text';

    const CRITERIA_titre = 'titre';
    const CRITERIA_numero_doctorant = 'doctorant-numero';
    const CRITERIA_nom_doctorant = 'doctorant-nom';
    const CRITERIA_prenom_doctorant = 'doctorant-prenom';
    const CRITERIA_nom_directeur = 'directeur-nom';
    const CRITERIA_code_ed = 'code-ed';
    const CRITERIA_code_ur = 'code_ur';

    /**
     * ATTENTION : les identifiants (clés) doivent être identiques à ceux utilisés dans
     *             le script de la vue matérialisée MV_RECHERCHE_THESE sollicitée pour la recherche.
     */
    const CRITERIA = [
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
    protected $availableCriteria = self::CRITERIA;

    /**
     * TheseTextSearchFilter constructor.
     * @param string $label
     * @param string $name
     */
    protected function __construct(string $label, string $name)
    {
        parent::__construct($label, $name);
    }

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        return new self(
            "Recherche de texte",
            self::NAME
        );
    }
}
