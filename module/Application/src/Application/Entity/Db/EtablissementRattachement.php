<?php

namespace Application\Entity\Db;

class EtablissementRattachement {

    /** @var integer $id **/
    private $id;
    /** @var UniteRecherche $unite **/
    protected $unite;
    /** @var Etablissement $etablissement **/
    protected $etablissement;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return UniteRecherche
     */
    public function getUnite()
    {
        return $this->unite;
    }

    /**
     * @param UniteRecherche $unite
     * @return EtablissementRattachement
     */
    public function setUnite($unite)
    {
        $this->unite = $unite;
        return $this;
    }

    /**
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     * @return EtablissementRattachement
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }
}