<?php

namespace Structure\Search\EcoleDoctorale;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\EcoleDoctorale;

/**
 * Filtre de type "école doctorale liée" (attribut : `sourceCode`).
 */
class EcoleDoctoraleSearchFilter extends SelectSearchFilter
{
    const NAME = 'ecoleDoctorale';

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
            "École doct.",
            self::NAME,
            ['liveSearch' => true]
        );

        $instance->setEmptyOptionLabel("Toutes");

        return $instance;
    }

    protected function applyToQueryBuilderUsingWhereField(QueryBuilder $qb)
    {
        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \Application\Search\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \Application\Search\Filter\SearchFilter::setWhereField()}
         */
        $filterValue = $this->getValue();
        if ($filterValue === 'NULL') {
            $qb->andWhere($this->whereField . " IS NULL");
        } elseif ($filterValue) {
            $qb
                ->andWhere($this->whereField . " = :ed_sourceCode")
                ->setParameter('ed_sourceCode', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'ED éventuellement spécifiée via $this->data est autorisée
            $sourceCodes = array_map(function(EcoleDoctorale $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($this->whereField, $sourceCodes));
        }
    }
}