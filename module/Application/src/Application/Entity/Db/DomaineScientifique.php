<?php

namespace Application\Entity\Db;


use Application\Search\Filter\SearchFilterValueInterface;

class DomaineScientifique implements SearchFilterValueInterface
{
    /** @var int */
    private $id;
    /** @var string */
    protected $libelle;
    /** @var \Doctrine\Common\Collections\Collection */
    protected $unites;

    public function __construct()
    {
        $this->unites = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

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
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return DomaineScientifique
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return \Structure\Entity\Db\UniteRecherche[]
     */
    public function getUnites()
    {
        return $this->unites->toArray();
    }

    /**
     * @param \Structure\Entity\Db\UniteRecherche $unite
     * @return DomaineScientifique
     */
    public function addUnite($unite)
    {
        $this->unites[] = $unite;
        return $this;
    }

    /**
     * @param \Structure\Entity\Db\UniteRecherche $unite
     * @return DomaineScientifique
     */
    public function removeUnite($unite)
    {
        $this->unites->removeElement($unite);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createSearchFilterValueOption(): array
    {
        return ['value' => (string) $this->getId(), 'label' => $this->getLibelle()];
    }
}