<?php

namespace Formation\Entity\Db;

use Individu\Entity\Db\Individu;
use Doctrine\Common\Collections\Collection;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Traits\HasModaliteTrait;
use Formation\Entity\Db\Traits\HasSiteTrait;
use Formation\Entity\Db\Traits\HasTypeTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Formation implements HistoriqueAwareInterface,
    HasSiteInterface, HasModaliteInterface, HasTypeInterface {
    use HistoriqueAwareTrait;
    use HasSiteTrait;
    use HasModaliteTrait;
    use HasTypeTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $libelle;

    /** @var string|null */
    private $description;

    /** @var string|null */
    private $lien;

    /** @var Module|null */
    private $module;
    /** @var Collection (Session) */
    private $sessions;

    /** @var Individu|null */
    private $responsable;

    /** @var int */
    private $tailleListePrincipale;
    /** @var int */
    private $tailleListeComplementaire;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     * @return Formation
     */
    public function setLibelle(?string $libelle): Formation
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Formation
     */
    public function setDescription(?string $description): Formation
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLien(): ?string
    {
        return $this->lien;
    }

    /**
     * @param string|null $lien
     * @return Formation
     */
    public function setLien(?string $lien): Formation
    {
        $this->lien = $lien;
        return $this;
    }

    /**
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * @param Module|null $module
     * @return Formation
     */
    public function setModule(?Module $module): Formation
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSessions() : Collection
    {
        return $this->sessions;
    }

    /**
     * @return Individu|null
     */
    public function getResponsable(): ?Individu
    {
        return $this->responsable;
    }

    /**
     * @param Individu|null $responsable
     * @return Formation
     */
    public function setResponsable(?Individu $responsable): Formation
    {
        $this->responsable = $responsable;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTailleListePrincipale(): ?int
    {
        return $this->tailleListePrincipale;
    }

    /**
     * @param int|null $tailleListePrincipale
     * @return Formation
     */
    public function setTailleListePrincipale(?int $tailleListePrincipale): Formation
    {
        $this->tailleListePrincipale = $tailleListePrincipale;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTailleListeComplementaire(): ?int
    {
        return $this->tailleListeComplementaire;
    }

    /**
     * @param int|null $tailleListeComplementaire
     * @return Formation
     */
    public function setTailleListeComplementaire(?int $tailleListeComplementaire): Formation
    {
        $this->tailleListeComplementaire = $tailleListeComplementaire;
        return $this;
    }


}