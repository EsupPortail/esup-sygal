<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use Zend\Form\Annotation;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * --- Class Anomalie ---
 */
class Anomalie
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $etablissementId;

    /**
     * @var string
     */
    private $tableName;


    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $tableColumn;

    /**
     * @var string
     */
    private $columnValue;

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
    public function getEtablissementId()
    {
        return $this->etablissementId;
    }

    /**
     * @param string $etablissementId
     * @return Anomalie
     */
    public function setEtablissementId($etablissementId)
    {
        $this->etablissementId = $etablissementId;
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
     * @return Anomalie
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @param string $sourceCode
     * @return Anomalie
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableColumn()
    {
        return $this->tableColumn;
    }

    /**
     * @param string $tableColumn
     * @return Anomalie
     */
    public function setTableColumn($tableColumn)
    {
        $this->tableColumn = $tableColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getColumnValue()
    {
        return $this->columnValue;
    }

    /**
     * @param string $columnValue
     * @return Anomalie
     */
    public function setColumnValue($columnValue)
    {
        $this->columnValue = $columnValue;
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
     * @return Anomalie
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }



}
