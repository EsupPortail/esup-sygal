<?php

namespace Structure\Search\Etablissement;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\Etablissement;

/**
 * Filtre de type "établissement lié" (attribut : `sourceCode`).
 */
class EtablissementSearchFilter extends SelectSearchFilter
{
    const NAME = 'etablissement';

    protected string $name = self::NAME;
    protected string $label = "Étab. d'inscr.";

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        // il ne peut y avoir qu'un seul champ pour ce filtre
        $whereField = current($this->whereFields);

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \Application\Search\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \Application\Search\Filter\SearchFilter::setWhereField()}
         */
        $qb
            ->andWhere($whereField . " = :etab_sourceCode")
            ->setParameter('etab_sourceCode', $this->getValue());

        if ($this->data !== null) {
            // garantit que l'étab éventuellement spécifié via $this->data est autorisé
            $sourceCodes = array_map(function(Etablissement $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($whereField, $sourceCodes));
        }
    }
}