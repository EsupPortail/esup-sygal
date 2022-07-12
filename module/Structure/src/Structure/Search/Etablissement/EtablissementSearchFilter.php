<?php

namespace Structure\Search\Etablissement;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\Etablissement;
use Webmozart\Assert\Assert;

/**
 * Filtre de type "établissement lié" (attribut : `sourceCode`).
 */
class EtablissementSearchFilter extends SelectSearchFilter
{
    const NAME = 'etablissement';

    /**
     * Instancie un filtre par établissement
     *
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
        return new self(
            "Étab. d'inscr.",
            self::NAME
        );
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $this->checkWhereField();

        /**
         * Pas de jointure en dur ici. Désormais, il faut :
         * - faire la jointure nécessaire dans {@see \Application\Search\SearchService::createQueryBuilder()} ;
         * - spécifier le champ sur lequel doit porter le 'where' via {@see \Application\Search\Filter\SearchFilter::setWhereField()}
         */
        $qb
            ->andWhere($this->whereField . " = :etab_sourceCode")
            ->setParameter('etab_sourceCode', $this->getValue());

        if ($this->data !== null) {
            // garantit que l'étab éventuellement spécifié via $this->data est autorisé
            $sourceCodes = array_map(function(Etablissement $entity) { return $entity->getSourceCode(); }, $this->data);
            $qb->andWhere($qb->expr()->in($this->whereField, $sourceCodes));
        }
    }
}