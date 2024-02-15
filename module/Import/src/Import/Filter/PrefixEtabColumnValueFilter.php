<?php

namespace Import\Filter;

use Application\Service\Source\SourceServiceAwareTrait;
use InvalidArgumentException;
use UnicaenDbImport\Filter\ColumnValue\AbstractColumnValueFilter;

/**
 * Filtre permettant de préfixer une valeur de colonne/attribut par le code établissement,
 * ex : 'UMR6211' devient 'UCN::UMR6211'.
 */
class PrefixEtabColumnValueFilter extends AbstractColumnValueFilter
{
    const PARAM_CODE_ETABLISSEMENT = 'code_etablissement';

    use SourceServiceAwareTrait;

    /**
     * @var string[]
     */
    protected array $columnsToTransform = [
        'sourceCode', 'source_code',
        'sourceId', 'source_id',
        'individuId', 'individu_id',
        'roleId', 'role_id',
        'theseId', 'these_id',
        'doctorantId', 'doctorant_id',
        'structureId', 'structure_id',
        'ecoleDoctId', 'ecole_doct_id',
        'uniteRechId', 'unite_rech_id',
        'acteurEtablissementId', 'acteur_etablissement_id',
        'origineFinancementId', 'origine_financement_id',
    ];

    protected ?string $codeEtablissement = null;

    public function __construct(?array $columnsToTransform = null)
    {
        if ($columnsToTransform !== null) {
            $this->columnsToTransform = $columnsToTransform;
        }
    }

    public function __toString(): string
    {
        return "Préfixage par le code établissement des colonnes/attributs suivants : " . PHP_EOL .
            implode(', ', $this->columnsToTransform);
    }

    public function setParams(array $params): void
    {
        if (array_key_exists($key = self::PARAM_CODE_ETABLISSEMENT, $params)) {
            $this->codeEtablissement = $params[self::PARAM_CODE_ETABLISSEMENT];
        }

        parent::setParams($params);
    }

    public function filter($value)
    {
        if ($value === null) {
            return null;
        }

        if ($this->codeEtablissement === null) {
            throw new InvalidArgumentException(sprintf(
                "Le paramètre '%s' n'a pas été fourni",
                self::PARAM_CODE_ETABLISSEMENT
            ));
        }

        if (in_array($this->columnName, $this->columnsToTransform)) {
            $value = $this->codeEtablissement . '::' . $value;
        }

        return $value;
    }
}