<?php

namespace Soutenance\Entity;

use UnicaenSearch\Filter\SearchFilterValueInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Etat implements HistoriqueAwareInterface, SearchFilterValueInterface
{
    use HistoriqueAwareTrait;

    public const EN_COURS_SAISIE = 'EN_COURS_SAISIE';
    public const EN_COURS_EXAMEN = 'EN_COURS_EXAMEN';
    public const ETABLISSEMENT = 'ETABLISSEMENT';
    public const VALIDEE = 'VALIDEE';
    public const REJETEE = 'REJETEE';
    public const COMPLET = 'COMPLET';

    /** @var integer */
    private $id;
    /** @var string */
    private $code;
    /** @var string */
    private $libelle;
    /** @var int */
    private $ordre;

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
     * @return Etat
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return Etat
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrdre(): int
    {
        return $this->ordre;
    }

    /**
     * @param int $ordre
     */
    public function setOrdre(int $ordre): void
    {
        $this->ordre = $ordre;
    }

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        return ['value' => $this->getCode(), 'label' => $this->getLibelle()];
    }
}