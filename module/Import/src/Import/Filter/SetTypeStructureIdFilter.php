<?php

namespace Import\Filter;

use Application\Service\Source\SourceServiceAwareTrait;
use InvalidArgumentException;
use Structure\Service\Structure\StructureServiceAwareTrait;
use UnicaenDbImport\Filter\ColumnValue\AbstractColumnValueFilter;

/**
 * Filtre permettant de retourner l'ID du type de structure voulue,
 * ex : '4' pour 'composante_rattachement'.
 */
class SetTypeStructureIdFilter extends AbstractColumnValueFilter
{
    const PARAM_CODE_TYPE_STRUCTURE = 'type-structure';

    use SourceServiceAwareTrait;
    use StructureServiceAwareTrait;

    /**
     * @var string[]
     */
    protected $columnsToTransform = [
        'typeId',
    ];

    /**
     * @var string
     */
    protected $codeTypeStructure;


    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return "Retourne l'ID du type de structure voulue : " . PHP_EOL .
            implode(', ', $this->columnsToTransform);
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params)
    {
        if (!isset($params[$key = self::PARAM_CODE_TYPE_STRUCTURE])) {
            throw new InvalidArgumentException("La clé '$key' doit exister dans les paramètres transmis");
        }

        $this->codeTypeStructure = $params[$key];

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
            $value = sprintf("(select id from type_structure where code like %s)",$this->codeTypeStructure);
            $value =  $this->structureService->getTypeStructureByCode($this->codeTypeStructure)->getCode();
        }

        return $value;
    }
}