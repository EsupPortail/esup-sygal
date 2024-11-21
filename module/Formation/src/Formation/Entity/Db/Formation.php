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
use JetBrains\PhpStorm\Pure;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Formation implements HistoriqueAwareInterface,
    HasSiteInterface, HasModaliteInterface, HasTypeInterface {
    use HistoriqueAwareTrait;
    use HasSiteTrait;
    use HasModaliteTrait;
    use HasTypeTrait;

    const TYPE_CODE_TRAVERSAL = 'T';
    const TYPE_CODE_SPECIFIQUE = 'S';

    private int $id;
    private ?string $libelle = null;
    private ?string $description  = null;
    private ?string $lien  = null;
    private ?Module $module  = null;
    private Collection $sessions;
    private ?Individu $responsable  = null;
    private ?int $tailleListePrincipale = null;
    private ?int $tailleListeComplementaire = null;

    private ?string $objectif = null;
    private ?string $programme = null;

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

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(?string $objectif): void
    {
        $this->objectif = $objectif;
    }

    public function getProgramme(): ?string
    {
        return $this->programme;
    }

    public function setProgramme(?string $programme): void
    {
        $this->programme = $programme;
    }

    /**
     * @return string
     */
    #[Pure] public function getCode() : string
    {
        $module = $this->getModule();
        return 'M'.$module->getId() . 'F'.$this->getId();
    }

    /** FONCTION POUR MACRO *******************************************************************************************/

    /** @noinspection  PhpUnused */
    public function toStringResponsable() : string
    {
        if ($this->getResponsable() === null) return "Aucun responsable de nommé·e pour cette formation";
        return $this->getResponsable()->getNomComplet();
    }


}