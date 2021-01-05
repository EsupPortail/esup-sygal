<?php

namespace Application\Service\These\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 *
 *
 * @author Unicaen
 */
class TheseTextFilter extends TheseFilter
{
    const NAME_text = 'text';
    const NAME_criteria = 'textCriteria';

    const CRITERIA_titre = 'titre';
    const CRITERIA_numero_doctorant = 'doctorant-numero';
    const CRITERIA_nom_doctorant = 'doctorant-nom';
    const CRITERIA_prenom_doctorant = 'doctorant-prenom';
    const CRITERIA_nom_directeur = 'directeur-nom';
    const CRITERIA_code_ed = 'code-ed';
    const CRITERIA_code_ur = 'code_ur';

    /**
     * Critères possibles sur lesquels faire porter la recherche sur texte libre.
     *
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
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams)
    {
        $filterValue = $this->paramFromQueryParams($queryParams);

        if (array_key_exists(self::NAME_criteria, $queryParams) && !empty($queryParams[self::NAME_criteria])) {
            $criteria = $queryParams[self::NAME_criteria];
        } else {
            $criteria = [];
        }

        $filterValue = [
            'text' => $filterValue,
            'criteria' => $criteria,
        ];

        $this->setValue($filterValue);
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        // not possible
    }
}
