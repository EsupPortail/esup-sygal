<?php

namespace Structure\Search\UniteRecherche;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\UniteRecherche;
use Webmozart\Assert\Assert;

/**
 * Filtre de type "unité de recherche liée" (attribut : `sourceCode`).
 */
class UniteRechercheSearchFilter extends SelectSearchFilter
{
    const NAME = 'uniteRecherche';

    /**
     * @inheritDoc
     */
    protected function __construct(string $label, string $name, array $attributes = [], $defaultValue = null)
    {
        parent::__construct($label, $name, $attributes, $defaultValue);
    }

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        $instance = new self(
            "Unit. rech.",
            self::NAME,
            ['liveSearch' => true]
        );

        $instance->setEmptyOptionLabel("Toutes");

        return $instance;
    }

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        Assert::maxCount($this->whereFields, 1,
            sprintf("La classe %s n'autorise qu'un seul 'where field'", get_class($this))
        );

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \Application\Search\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \Application\Search\Filter\SearchFilter::setWhereField()}
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