<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * Classe mère abstraite de tous les filtres de recherche.
 */
abstract class SearchFilter implements SearchFilterInterface
{
    const OPERATOR_OR = 'OR';
    const OPERATOR_AND = 'AND';

    protected string $name;
    protected string $label;
    protected mixed $value;
    protected mixed $defaultValue = null;
    protected array $attributes = [];
    protected bool $visible = true;

    /**
     * Callable appelé par le moteur pour appliquer ce filtre au query builder.
     * @var callable|null
     */
    protected $applyToQueryBuilderCallable = null;

    /**
     * Eventuels champs d'entités sur lesquel portent les tests de valeur, ainsi que l'opérateur logique à appliquer
     * entre les tests correspondants.
     * Ex : `[ ['f.nom', 'f.nomOriginal'] ]`, équivalent à `[ static::OPERATOR_OR => ['f.nom', 'f.nomOriginal'] ]`
     */
    protected ?array $whereFields = null;

    /**
     * Constructeur.
     *
     * @param string|null $label Label/intitlulé du filtre, ex : "Nom du fichier"
     * @param string|null $name Petit nom du filtre (unique), ex : 'nom_fichier'
     * @param array $attributes Attributs de ce filtre, ex : ['class' => 'selectpicker']
     * @param mixed $defaultValue Valeur par défaut de ce filtre
     */
    public function __construct(?string $label = null, ?string $name = null, array $attributes = [], mixed $defaultValue = null)
    {
        if ($label !== null) {
            $this->setLabel($label);
        }
        if ($name !== null) {
            $this->setName($name);
        }

        $this->setAttributes($attributes);
        $this->setDefaultValue($defaultValue);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retourne la valeur par défaut (pré-remplie) de ce filtre.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Spécifie la valeur par défaut (pré-remplie) de ce filtre.
     */
    public function setDefaultValue(mixed $defaultValue = null): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes, bool $overwrite = false): static
    {
        $this->attributes = $overwrite ? $attributes : array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Indique si ce filtre est sensé être visible ou non.
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Spécifie si ce filtre est sensé être visible ou non.
     */
    public function setVisible(bool $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Spécifie le champ sur lequel porte le test de valeur.
     *
     * @param string $whereField Ex : 'f.nom'
     */
    public function setWhereField(string $whereField): static
    {
        return $this->setWhereFields([$whereField]);
    }

    /**
     * Spécifie les champs d'entités sur lesquel portent les tests de valeur, ainsi que l'opérateur logique à appliquer
     * entre les tests correspondants.
     *
     * @param array $whereFields Liste des champs concernés, ex : ['f.nom', 'f.nomOriginal']
     * @param string|null $operator Par défaut, c'est l'opérateur OR ({@see static::OPERATOR_OR})
     */
    public function setWhereFields(array $whereFields, ?string $operator = self::OPERATOR_OR): static
    {
        Assert::inArray($operator, [self::OPERATOR_AND, self::OPERATOR_OR]);

        $this->whereFields = [$operator => $whereFields];

        return $this;
    }

    /**
     * Initialisation du filtre.
     */
    public function init(): static
    {
        // initialisations utiles
        // ...

        return $this;
    }

    /**
     * Spécifie le Callable appelé par le moteur pour appliquer ce filtre au query builder.
     */
    public function setQueryBuilderApplier(callable $applyToQueryBuilderCallable): static
    {
        $this->applyToQueryBuilderCallable = $applyToQueryBuilderCallable;

        return $this;
    }

    /**
     * Applique ou exploite les 'query params' spécifiés.
     */
    public function processQueryParams(array $queryParams): void
    {
        $filterValue = $this->paramFromQueryParams($queryParams);

        $this->setValue($filterValue);
    }

    /**
     * Cherche parmi les query params valués spécifiés si l'un correspond au nom de ce filtre,
     * et retourne la valeur associée le cas échéant.
     */
    public function paramFromQueryParams(array $queryParams): ?string
    {
        $name = $this->getName();

        if (! array_key_exists($name, $queryParams)) {
            // null <=> paramètre absent
            return null;
        }

        // NB: '' <=> "Tous"

        return $queryParams[$name];
    }

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        if ($this->applyToQueryBuilderCallable !== null) {
            $this->applyToQueryBuilderUsingCallable($qb);
            return;
        }

        if ($this->whereFields !== null) {
            // utilisation des champs de conditions spécifiés
            $this->applyToQueryBuilderUsingWhereFields($qb);
        } else {
            // tentative de construction automatique du where
            $this->applyToQueryBuilderByDefault($qb);
        }
    }

    /**
     * Utilisation du callable pour appliquer ce filtre au query builder spécifié.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     */
    protected function applyToQueryBuilderUsingCallable(QueryBuilder $qb): void
    {
        $applyToQueryBuilder = $this->applyToQueryBuilderCallable;
        $applyToQueryBuilder($this, $qb);
    }

    /**
     * Application de ce filtre au query builder spécifié, en utilisant le ou les champs de condition fourni·s.
     */
    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        if (is_int(key($this->whereFields))) {
            $this->whereFields = [static::OPERATOR_OR => $this->whereFields];
        }

        $operator = key($this->whereFields);
        $operator = is_int($operator) ? self::OPERATOR_OR : $operator;

        $whereFields = $this->whereFields[$operator];

        [$parts, $params] = $this->generateQueryBuilderExprAndParamsFromWhereFields($whereFields);
        Assert::isArray($parts);
        Assert::isArray($params);

        if (count($parts) === 1) {
            $expr = reset($parts);
        } else {
            $expr = $operator === static::OPERATOR_OR ?
                $qb->expr()->orX(...$parts) :
                $qb->expr()->andX(...$parts);
        }
        $qb->andWhere($expr);

        foreach ($params as $paramName => $paramValue) {
            $qb->setParameter($paramName, $paramValue);
        }
    }

    /**
     * Application par défaut de ce filtre au query builder spécifié, avec construction automatique du where.
     */
    protected function applyToQueryBuilderByDefault(QueryBuilder $qb): void
    {
        $alias = current($qb->getRootAliases());
        $template = $this->generateComparisonTemplate('%s.%s');

        $paramName = $this->generateParameterName();
        if ($paramName !== null) {
            $qb
                ->andWhere(sprintf($template, $alias, $this->getName(), $paramName))
                ->setParameter($paramName, $this->generateComparisonValue());
        } else {
            $qb->andWhere(sprintf($template, $alias, $this->getName()));
        }
    }

    /**
     * Génère, à partir des champs de condition spécifié, les expressions DQL intermédiaires et les paramètres
     * associés éventuels qui seront uilisés pour former la clause de filtrage.
     *
     * @param array $whereFields Ex: ['f.nom', 'f.nomOriginal']
     * @return array Liste contenant 2 éléments : la liste des expressions DQL générées ; le tableau associatifs des paramètres éventuels.
     * Ex : `[ ['f.nom = :p65df4', 'f.nomOriginal = :p324sd'], ['p65df4' => 'manuscrit', 'p324sd' => 'manuscrit']]`
     */
    protected function generateQueryBuilderExprAndParamsFromWhereFields(array $whereFields): array
    {
        $template = $this->generateComparisonTemplate('%s');

        $exprs = [];
        $params = [];
        foreach ($whereFields as $field) {
            $paramName = $this->generateParameterName();
            if ($paramName !== null) {
                $exprs[] = sprintf($template, $field, $paramName);
                $params[$paramName] = $this->generateComparisonValue();
            } else {
                $exprs[] = sprintf($template, $field);
            }
        }

        return [$exprs, $params];
    }

    /**
     * Génère un nom de paramètre DQL ; ou `null` si aucun paramètre n'est nécessaire.
     */
    protected function generateParameterName(): ?string
    {
        return uniqid('p');
    }

    /**
     * Génère le template de code DQL réalisant uniquement la comparaison, à partir de l'opérande (de gauche) spécifiée.
     */
    protected function generateComparisonTemplate(string $operand): string
    {
        return "$operand = :%s";
    }

    /**
     * Génère le bout de code DQL correspondant uniquement à la valeur courante de ce filtre,
     * exploité pour générer la comparaison.
     */
    public function generateComparisonValue(): mixed
    {
        return $this->getValue();
    }

    /**
     * Indique si ce filtre peut être appliqué au query builder.
     */
    public function canApplyToQueryBuilder(): bool
    {
        return (bool) $this->getValue();
    }
}
