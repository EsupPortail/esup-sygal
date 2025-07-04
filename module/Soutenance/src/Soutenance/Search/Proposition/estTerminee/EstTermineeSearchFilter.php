<?php

namespace Soutenance\Search\Proposition\estTerminee;

use Doctrine\ORM\QueryBuilder;
use UnicaenSearch\Filter\CheckboxSearchFilter;

/**
 * Filtre de type case à cocher dont le fonctionnement est le suivant :
 *   - s'il est décoché : n'ajoute rien au query builder.
 *   - s'il est coché : ajoute au query builder une clause écartant les propositions dont la thèse/HDR est en cours ;
 */
class EstTermineeSearchFilter extends CheckboxSearchFilter
{
    public const NAME = 'estTerminee';

    protected string $name = self::NAME;
    protected string $label = "Soutenances terminées incluses";

    /** @var string Le préfixe d'alias de l'entité dans le QueryBuilder (ex: "hdr", "these") */
    protected string $entityAlias;

    /** @var string Le nom du champ à filtrer (ex: "etatHDR", "etatThese") */
    protected string $stateField = 'etatThese';

    /** @var array les états sur lesquels filtrer */
    protected array $etats = [];

    public function __construct(string $entityAlias = 'these', string $stateField = 'etatThese', array $etats = [])
    {
        parent::__construct();

        $this->entityAlias = $entityAlias;
        $this->stateField = $stateField;
        $this->etats = $etats;
    }

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        if ($this->getValue()) {
            $field = "{$this->entityAlias}.{$this->stateField}";
            $qb->andWhere("$field in (:$this->stateField)")->setParameter($this->stateField, $this->etats);
        }
    }
}