<?php

namespace Structure\Search\UniteRecherche;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\UniteRecherche;

/**
 * Filtre de type "unité de recherche liée" (attribut : `sourceCode`).
 */
class UniteRechercheSearchFilter extends SelectSearchFilter
{
    const NAME = 'uniteRecherche';

    protected string $name = self::NAME;
    protected string $label = "Unit. rech.";
    protected array $attributes = ['liveSearch' => true];
    protected string $emptyOptionLabel = "Toutes";

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        // il ne peut y avoir qu'un seul champ pour ce filtre
        $whereField = current($this->whereFields);

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \Application\Search\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \Application\Search\Filter\SearchFilter::setWhereField()}
         */
        $filterValue = $this->getValue();
        if ($filterValue === 'NULL') {
            $qb->andWhere($whereField . " IS NULL");
        } elseif ($filterValue) {
            $qb
                ->andWhere($whereField . " = :ur_sourceCode")
                ->setParameter('ur_sourceCode', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'UR éventuellement spécifiée via $this->data est autorisée
            $sourceCodes = array_map(function(UniteRecherche $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($whereField, $sourceCodes));
        }
    }
}