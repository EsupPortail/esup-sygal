<?php

namespace Formation\Entity\Db;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Structure;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Formation implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

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

    /** @var Etablissement|null */
    private $site;
    /** @var Individu|null */
    private $responsable;
    /** @var string|null */
    private $modalite;
    /** @var string|null */
    private $type;
    /** @var Structure|null */
    private $typeStructure;
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
     * @return Collection
     */
    public function getSessions() : Collection
    {
        return $this->sessions;
    }

    /**
     * @return Etablissement|null
     */
    public function getSite(): ?Etablissement
    {
        return $this->site;
    }

    /**
     * @param Etablissement|null $site
     * @return Formation
     */
    public function setSite(?Etablissement $site): Formation
    {
        $this->site = $site;
        return $this;
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
     * @return mixed
     */
    public function getModalite()
    {
        return $this->modalite;
    }

    /**
     * @param string|null $modalite
     * @return Formation
     */
    public function setModalite(?string $modalite) : Formation
    {
        $this->modalite = $modalite;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Formation
     */
    public function setType(?string $type) : Formation
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Structure|null
     */
    public function getTypeStructure(): ?Structure
    {
        return $this->typeStructure;
    }

    /**
     * @param Structure|null $typeStructure
     * @return Formation
     */
    public function setTypeStructure(?Structure $typeStructure): Formation
    {
        $this->typeStructure = $typeStructure;
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