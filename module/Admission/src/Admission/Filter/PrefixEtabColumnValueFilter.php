<?php

namespace Admission\Filter;

use Application\Service\Source\SourceServiceAwareTrait;
use InvalidArgumentException;
use UnicaenDbImport\Filter\ColumnValue\AbstractColumnValueFilter;

/**
 * Filtre permettant de préfixer une valeur de colonne/attribut par le code établissement,
 * ex : 'UMR6211' devient 'UCN::UMR6211'.
 */
class PrefixEtabColumnValueFilter extends AbstractColumnValueFilter
{
    const PARAM_CODE_ETABLISSEMENT = 'code_etablissement_admission';

    use SourceServiceAwareTrait;

    /**
     * @var string[]
     */
    protected $columnsToTransform = [
        'code'
    ];

    /**
     * @var string
     */
    protected $codeEtablissement;

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return "Préfixage par le code établissement des colonnes/attributs suivants : " . PHP_EOL .
            implode(', ', $this->columnsToTransform);
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params)
    {
        if (!isset($params[$key = self::PARAM_CODE_ETABLISSEMENT])) {
            throw new InvalidArgumentException("La clé '$key' doit exister dans les paramètres transmis");
        }

        $this->codeEtablissement = $params[$key];

        parent::setParams($params);
    }

    /**
     * @inheritDoc
     */
    public function filter(string $name, $value)
    {
        if ($value === null) {
            return null;
        }

        if (in_array($name, $this->columnsToTransform)) {
            $value = $this->codeEtablissement . '::' . $value;
        }

        return $value;
    }
}