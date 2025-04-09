<?php

namespace Structure\Search\EcoleDoctorale;

use UnicaenSearch\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\EcoleDoctorale;

/**
 * Filtre de type "école doctorale liée" (attribut : `sourceCode`).
 */
class EcoleDoctoraleSearchFilter extends SelectSearchFilter
{
    public const NAME = 'ecoleDoctorale';

    protected string $name = self::NAME;
    protected string $label = "École doct.";
    protected array $attributes = ['liveSearch' => true];
    protected string $emptyOptionLabel = "Toutes";

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        // il ne peut y avoir qu'un seul champ pour ce filtre
        $whereField = current($this->whereFields);

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \UnicaenSearch\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \UnicaenSearch\Filter\SearchFilter::setWhereField()}
         */
        $filterValue = $this->getValue();
        if ($filterValue === 'NULL') {
            $qb->andWhere($whereField . " IS NULL");
        } elseif ($filterValue) {
            $qb
                ->andWhere($whereField . " = :ed_sourceCode")
                ->setParameter('ed_sourceCode', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'ED éventuellement spécifiée via $this->data est autorisée
            $sourceCodes = array_map(function(EcoleDoctorale $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($whereField, $sourceCodes));
        }
    }
}