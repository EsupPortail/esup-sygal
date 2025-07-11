<?php

namespace Application\Search\AnneeUniv;

use Application\Entity\AnneeUniv;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Doctrine\ORM\QueryBuilder;
use UnicaenSearch\Filter\SearchFilter;
use UnicaenSearch\Filter\SelectSearchFilter;
use Webmozart\Assert\Assert;

/**
 * Filtre portant sur l'année universitaire.
 *
 * Deux modes de fonctionnement possibles :
 * - mode habituel : en utilisant uniquement `setWhereField·s()`, le filtrage des données se fait classiquement
 * sur l'année universitaire sélectionnée elle-même ;
 * - mode dates min/max : en utilisant par ex `setWhereFieldDateMin('a.debut')` et/ou `setWhereFieldDateMax('a.fin')`,
 * le filtrage appliqué sera "a.debut >= date de début de l'année univ" et/ou "a.fin <= date de fin de l'année univ".
 */
class AnneeUnivSearchFilter extends SelectSearchFilter
{
    use AnneeUnivServiceAwareTrait;

    public const NAME = 'anneeUniv';

    protected string $name = self::NAME;
    protected string $label = "Année univ.";

    protected ?string $whereFieldDateMin = null;
    protected ?string $whereFieldDateMax = null;

    public function setWhereFieldDateMin(string $whereField): static
    {
        Assert::isEmpty($this->whereFields, "Méthode inutilisable car au moins un where field a déjà été spécifié.");

        $this->whereFieldDateMin = $whereField;
        $this->whereFieldsWithoutOperator[0] = $whereField;

        return $this;
    }

    public function setWhereFieldDateMax(string $whereField): static
    {
        Assert::isEmpty($this->whereFields, "Méthode inutilisable car au moins un where field a déjà été spécifié.");

        $this->whereFieldDateMax = $whereField;
        $this->whereFieldsWithoutOperator[1] = $whereField;

        return $this;
    }

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $this->applyToQueryBuilderUsingWhereFields($qb);
    }

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
        if ($this->whereFieldDateMin !== null || $this->whereFieldDateMax !== null) {
            $this->whereFields = [SearchFilter::OPERATOR_AND => $this->whereFieldsWithoutOperator];
        }

        parent::applyToQueryBuilderUsingWhereFields($qb);
    }

    protected function generateQueryBuilderExprAndParamsFromWhereFields(array $whereFields): array
    {
        if ($this->whereFieldDateMin === null && $this->whereFieldDateMax === null) {
            return parent::generateQueryBuilderExprAndParamsFromWhereFields($whereFields);
        }

        $value = $this->getValue();
        $exprs = [];
        $params = [];

        if ($this->whereFieldDateMin !== null) {
            $param0 = $this->generateParameterName();
            $exprs[] = $this->whereFieldDateMin . ' >= :' . $param0; // ex : 'alias.debut >= :p6d54f6sd4f
            $params[$param0] = $value->getDateDebut();
        }
        if ($this->whereFieldDateMax !== null) {
            $param1 = $this->generateParameterName();
            $exprs[] = $this->whereFieldDateMax . ' <= :' . $param1; // ex : 'alias.fin <= :p65sq4d6s
            $params[$param1] = $value->getDateFin();
        }

        return [$exprs, $params];
    }

    public function generateComparisonValue(): mixed
    {
        // NB : cette méthode n'est pas sollicitée lorsque setWhereFieldDateMin() ou setWhereFieldDateMax() est utilisée.

        $value = $this->getValue();

        if ($value instanceof AnneeUniv) {
            return $value->getPremiereAnnee();
        }

        return $value;
    }

    public function setDefaultValue(mixed $defaultValue = null): static
    {
        $defaultValue = $this->normalizeInputValue($defaultValue);

        return parent::setDefaultValue($defaultValue);
    }

    public function setValue($value): static
    {
        $value = $this->normalizeInputValue($value);

        return parent::setValue($value);
    }

    protected function normalizeInputValue($value)
    {
        if ($this->multiple) {
            foreach ((array)$value as $k => $v) {
                $value[$k] = $v instanceof AnneeUniv ? $v->getPremiereAnnee() : $v;
            }
        } else {
            $value = $value instanceof AnneeUniv ? $value->getPremiereAnnee() : $value;
        }

        return $value;
    }

    /**
     * Retourne la ou les {@see AnneeUniv} sélectionnée·s (le cas écéhant),
     * ou 'NULL' si la valeur {@see SelectSearchFilter::VALUE_NONE} est sélectionnée,
     * ou `null` si rien n'est sélectionné.
     */
    public function getValue(): string|AnneeUniv|array|null
    {
        $value = parent::getValue();

        if ($value === null) {
            return null;
        }
        if ($value === SelectSearchFilter::VALUE_NONE) {
            return $value;
        }

        if ($this->multiple) {
            $value = array_map(fn($v) => $this->createAnneeUnivFromPremiereAnnee((int)$v), $value);
        } else {
            $value = $this->createAnneeUnivFromPremiereAnnee((int)$value);
        }

        return $value;
    }

    protected function createAnneeUnivFromPremiereAnnee(int $annee): AnneeUniv
    {
        $anneeUniv = AnneeUniv::fromPremiereAnnee($annee);
        $anneeUniv->setDateDebut($this->anneeUnivService->computeDateDebut($anneeUniv));
        $anneeUniv->setDateFin($this->anneeUnivService->computeDateFin($anneeUniv));

        return $anneeUniv;
    }
}