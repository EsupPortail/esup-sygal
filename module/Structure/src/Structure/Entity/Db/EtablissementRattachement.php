<?php

namespace Structure\Entity\Db;

class EtablissementRattachement {

    /** @var integer $id **/
    private $id;
    /** @var \Structure\Entity\Db\UniteRecherche $unite **/
    protected $unite;
    /** @var \Structure\Entity\Db\Etablissement $etablissement **/
    protected $etablissement;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retourne l'éventuel UR liée.
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        return $this->unite;
    }

    /**
     * @param \Structure\Entity\Db\UniteRecherche $unite
     * @return EtablissementRattachement
     */
    public function setUniteRecherche(UniteRecherche $unite): self
    {
        $this->unite = $unite;
        return $this;
    }

    /**
     * Retourne l'éventuel établissement lié.
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param \Structure\Entity\Db\Etablissement $etablissement
     * @return EtablissementRattachement
     */
    public function setEtablissement(Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }
}