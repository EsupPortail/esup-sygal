<?php

namespace Structure\Search\UniteRecherche;

use UnicaenSearch\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\UniteRecherche;
use Webmozart\Assert\Assert;

/**
 * Filtre de type "unité de recherche liée" (attribut : `sourceCode`).
 */
class UniteRechercheSearchFilter extends SelectSearchFilter
{
    public const NAME = 'uniteRecherche';

    protected string $name = self::NAME;
    protected string $label = "Unit. rech.";
    protected array $attributes = ['liveSearch' => true];
    protected string $emptyOptionLabel = "Toutes";

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        Assert::maxCount($this->whereFields, 1,
            sprintf("La classe %s n'autorise qu'un seul 'where field'", get_class($this))
        );

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \UnicaenSearch\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \UnicaenSearch\Filter\SearchFilter::setWhereField()}
         */
        $filterValue = $this->getValue();
        if ($filterValue === 'NULL') {
            $qb->andWhere($this->whereFields[0] . " IS NULL");
        } elseif ($filterValue) {
            $qb
                ->andWhere($this->whereFields[0] . " = :ur_sourceCode")
                ->setParameter('ur_sourceCode', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'UR éventuellement spécifiée via $this->data est autorisée
            $sourceCodes = array_map(function(UniteRecherche $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($this->whereFields[0], $sourceCodes));
        }
    }
}