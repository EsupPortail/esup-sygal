<?php

namespace Structure\Entity\Db;

use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * ComposanteEnseignement
 */
class ComposanteEnseignement implements StructureConcreteInterface, HistoriqueAwareInterface, SourceAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use StructureAwareTrait;
    /**
     * @var string|null
     */
    private $sourceCode;

    /**
     * @var int
     */
    private $id;

    /**
     * ComposanteEnseignement constructor.
     */
    public function __construct()
    {
        $this->structure = new Structure();
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'ComposanteEnseignement';
    }

    /**
     * Set sourceCode.
     *
     * @param string|null $sourceCode
     *
     * @return ComposanteEnseignement
     */
    public function setSourceCode($sourceCode = null)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode.
     *
     * @return string|null
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString(): string
    {
        $str = '';
        if ($sigle = $this->structure->getSigle()) {
            $str .= "$sigle - ";
        }
        $str .= $this->structure->getLibelle();
        return $str;
    }

    public function getCode(): string
    {
        return $this->structure->getCode();
    }
}
