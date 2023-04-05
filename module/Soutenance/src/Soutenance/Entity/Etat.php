<?php

namespace Soutenance\Entity;

use Application\Search\Filter\SearchFilterValueInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Etat implements HistoriqueAwareInterface, SearchFilterValueInterface
{
    use HistoriqueAwareTrait;

    const EN_COURS = 'EN_COURS';
    const ETABLISSEMENT = 'ETABLISSEMENT';
    const VALIDEE = 'VALIDEE';
    const REJETEE = 'REJETEE';
    const COMPLET = 'COMPLET';

    /** @var integer */
    private $id;
    /** @var string */
    private $code;
    /** @var string */
    private $libelle;

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
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        return ['value' => $this->getCode(), 'label' => $this->getLibelle()];
    }
}