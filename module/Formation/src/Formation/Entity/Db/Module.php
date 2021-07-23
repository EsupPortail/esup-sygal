<?php

namespace Formation\Entity\Db;

use Application\Entity\Db\Individu;
use Doctrine\Common\Collections\Collection;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Traits\HasModaliteTrait;
use Formation\Entity\Db\Traits\HasSiteTrait;
use Formation\Entity\Db\Traits\HasTypeTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Module implements HistoriqueAwareInterface,
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
     * @return Module
     */
    public function setLibelle(?string $libelle): Module
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
     * @return Module
     */
    public function setDescription(?string $description): Module
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
     * @return Module
     */
    public function setLien(?string $lien): Module
    {
        $this->lien = $lien;
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
     * @return Module
     */
    public function setResponsable(?Individu $responsable): Module
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
     * @return Module
     */
    public function setTailleListePrincipale(?int $tailleListePrincipale): Module
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
     * @return Module
     */
    public function setTailleListeComplementaire(?int $tailleListeComplementaire): Module
    {
        $this->tailleListeComplementaire = $tailleListeComplementaire;
        return $this;
    }


}