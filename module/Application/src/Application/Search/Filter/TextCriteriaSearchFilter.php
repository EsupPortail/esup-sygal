<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 *
 *
 * @author Unicaen
 */
class TextCriteriaSearchFilter extends SearchFilter
{
    const NAME_text = 'text';
    const NAME_criteria = 'textCriteria';

    /**
     * Critères possibles sur lesquels faire porter la recherche sur texte libre.
     *
     * ATTENTION : les identifiants (clés) doivent être identiques à ceux utilisés dans
     *             le script de la vue matérialisée MV_RECHERCHE_THESE sollicitée pour la recherche.
     */
    const CRITERIA = [
        'titre' => "Titre de la thèse",
        'doctorant-numero' => "Numéro étudiant du doctorant",
        'doctorant-nom' => "Nom d'usage ou patronymique du doctorant",
        'doctorant-prenom' => "Prénom du doctorant",
        'directeur-nom' => "Nom d'usage ou patronymique du directeur ou co-directeur de thèse",
        'code-ed' => "Code national de l'école doctorale concernée (ex: 181)",
        'code-ur' => "Unité de recherche concernée (ex: umr6211)",
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
            $criteria = array_keys(self::CRITERIA);
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
