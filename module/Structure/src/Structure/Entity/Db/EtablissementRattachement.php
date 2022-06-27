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
     * @return \Structure\Entity\Db\UniteRecherche
     */
    public function getUnite()
    {
        return $this->unite;
    }

    /**
     * @param \Structure\Entity\Db\UniteRecherche $unite
     * @return EtablissementRattachement
     */
    public function setUnite($unite)
    {
        $this->unite = $unite;
        return $this;
    }

    /**
     * @return \Structure\Entity\Db\Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param \Structure\Entity\Db\Etablissement $etablissement
     * @return EtablissementRattachement
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }
}