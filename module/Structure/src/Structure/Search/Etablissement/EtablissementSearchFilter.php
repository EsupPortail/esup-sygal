<?php

namespace Structure\Search\Etablissement;

use UnicaenSearch\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\Etablissement;
use Webmozart\Assert\Assert;

/**
 * Filtre de type "établissement lié" (attribut : `sourceCode`).
 */
class EtablissementSearchFilter extends SelectSearchFilter
{
    public const NAME = 'etablissement';

    protected string $name = self::NAME;
    protected string $label = "Étab. d'inscr.";

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        Assert::maxCount($this->whereFieldsWithoutOperator, 1,
            sprintf("La classe %s n'autorise qu'un seul 'where field'", get_class($this))
        );

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \UnicaenSearch\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \UnicaenSearch\Filter\SearchFilter::setWhereField()}
         */
        $qb
            ->andWhere($this->whereFieldsWithoutOperator[0] . " = :etab_sourceCode")
            ->setParameter('etab_sourceCode', $this->getValue());

        if ($this->data !== null) {
            // garantit que l'étab éventuellement spécifié via $this->data est autorisé
            $sourceCodes = array_map(function(Etablissement $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($this->whereFieldsWithoutOperator[0], $sourceCodes));
        }
    }
}