<?php

namespace Application\Entity\Db;

class ImportObserv
{
    const OPERATION_UPDATE = 'UPDATE';

    const CODE_RESULTAT_PASSE_A_ADMIS = 'RESULTAT_PASSE_A_ADMIS';
    const CODE_CORRECTION_PASSE_A_FACULTATIVE = 'CORRECTION_PASSE_A_FACULTATIVE';
    const CODE_CORRECTION_PASSE_A_OBLIGATOIRE = 'CORRECTION_PASSE_A_OBLIGATOIRE';

    const CODES = [
        self::CODE_RESULTAT_PASSE_A_ADMIS,
        self::CODE_CORRECTION_PASSE_A_FACULTATIVE,
        self::CODE_CORRECTION_PASSE_A_OBLIGATOIRE,
    ];

    const EVENT_NAME = 'import-notification-event';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $columnName;

    /**
     * @var string
     */
    private $operation;

    /**
     * @var string
     */
    private $toValue;

    /**
     * @var string
     */
    private $description;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @param string $columnName
     * @return $this
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * @return string
     */
    public function getToValue()
    {
        return $this->toValue;
    }

    /**
     * @param string $toValue
     * @return $this
     */
    public function setToValue($toValue)
    {
        $this->toValue = $toValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
