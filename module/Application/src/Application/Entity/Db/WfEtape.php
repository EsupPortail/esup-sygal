<?php

namespace Application\Entity\Db;

use Application\Entity\Db\Interfaces\EtapeEtatAwareInterface;
use Application\Entity\Db\Interfaces\EtapeEtatAwareTrait;

/**
 * WfEtape
 */
class WfEtape implements EtapeEtatAwareInterface
{
    use EtapeEtatAwareTrait;

    const CODE_VALIDATION_PAGE_DE_COUVERTURE                    = 'VALIDATION_PAGE_DE_COUVERTURE';

    const CODE_DEPOT_VERSION_ORIGINALE                          = 'DEPOT_VERSION_ORIGINALE';
    const CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE                 = 'DEPOT_VERSION_ORIGINALE_CORRIGEE';

    const CODE_ATTESTATIONS                                     = 'ATTESTATIONS';
    const CODE_ATTESTATIONS_VERSION_CORRIGEE                    = 'ATTESTATIONS_VERSION_CORRIGEE';

    const CODE_AUTORISATION_DIFFUSION_THESE                     = 'AUTORISATION_DIFFUSION_THESE';
    const CODE_AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE    = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE';

    const CODE_SIGNALEMENT_THESE                                = 'SIGNALEMENT_THESE';

    const CODE_ARCHIVABILITE_VERSION_ORIGINALE                  = 'ARCHIVABILITE_VERSION_ORIGINALE';
    const CODE_ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE         = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE';

    const CODE_DEPOT_VERSION_ARCHIVAGE                          = 'DEPOT_VERSION_ARCHIVAGE';
    const CODE_DEPOT_VERSION_ARCHIVAGE_CORRIGEE                 = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE';

    const CODE_ARCHIVABILITE_VERSION_ARCHIVAGE                  = 'ARCHIVABILITE_VERSION_ARCHIVAGE';
    const CODE_ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE         = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE';

    const CODE_VERIFICATION_VERSION_ARCHIVAGE                   = 'VERIFICATION_VERSION_ARCHIVAGE';
    const CODE_VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE          = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE';

    const CODE_RDV_BU_SAISIE_DOCTORANT                          = 'RDV_BU_SAISIE_DOCTORANT';
//    const CODE_RDV_BU_SAISIE_BU                                 = 'RDV_BU_SAISIE_BU';
    const CODE_RDV_BU_VALIDATION_BU                             = 'RDV_BU_VALIDATION_BU';

    const CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT      = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT';
    const CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR      = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR';

    const PSEUDO_ETAPE_FINALE                                   = 'pseudo-etape-finale';

    const CODE_REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE          = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $libelleActeur;

    /**
     * @var string
     */
    private $libelleAutres;

    /**
     * @var integer
     */
    private $ordre;

    /**
     * @var integer
     */
    private $chemin;

    /**
     * @var string
     */
    private $route;

    /**
     * @var boolean
     */
    private $obligatoire;

    /**
     * @var string
     */
    private $descNonFranchie;

    /**
     * @var string
     */
    private $descSansObjectif;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var bool
     */
    private $estPseudoEtapeFinale = false;

    public function __toString()
    {
        return $this->getLibelleActeur();
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return WfEtape
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelleActeur
     *
     * @param string $libelleActeur
     *
     * @return WfEtape
     */
    public function setLibelleActeur($libelleActeur)
    {
        $this->libelleActeur = $libelleActeur;

        return $this;
    }

    /**
     * Get libelleActeur
     *
     * @return string
     */
    public function getLibelleActeur()
    {
        return $this->libelleActeur;
    }

    /**
     * Set libelleAutres
     *
     * @param string $libelleAutres
     *
     * @return WfEtape
     */
    public function setLibelleAutres($libelleAutres)
    {
        $this->libelleAutres = $libelleAutres;

        return $this;
    }

    /**
     * Get libelleAutres
     *
     * @return string
     */
    public function getLibelleAutres()
    {
        return $this->libelleAutres;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return WfEtape
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * @return int
     */
    public function getChemin()
    {
        return $this->chemin;
    }

    /**
     * @param int $chemin
     * @return $this
     */
    public function setChemin($chemin)
    {
        $this->chemin = $chemin;

        return $this;
    }

    /**
     * Set route
     *
     * @param string $route
     *
     * @return WfEtape
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set obligatoire
     *
     * @param boolean $obligatoire
     *
     * @return WfEtape
     */
    public function setObligatoire($obligatoire)
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    /**
     * Get obligatoire
     *
     * @return boolean
     */
    public function getObligatoire()
    {
        return $this->obligatoire;
    }

    /**
     * Set descNonFranchie
     *
     * @param string $descNonFranchie
     *
     * @return WfEtape
     */
    public function setDescNonFranchie($descNonFranchie)
    {
        $this->descNonFranchie = $descNonFranchie;

        return $this;
    }

    /**
     * Get descNonFranchie
     *
     * @return string
     */
    public function getDescNonFranchie()
    {
        return $this->descNonFranchie;
    }

    /**
     * Set descSansObjectif
     *
     * @param string $descSansObjectif
     *
     * @return WfEtape
     */
    public function setDescSansObjectif($descSansObjectif)
    {
        $this->descSansObjectif = $descSansObjectif;

        return $this;
    }

    /**
     * Get descSansObjectif
     *
     * @return string
     */
    public function getDescSansObjectif()
    {
        return $this->descSansObjectif;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function estPseudoEtapeFinale()
    {
        return $this->estPseudoEtapeFinale;
    }

    /**
     * @return static
     */
    public static function pseudoEtapeFinale()
    {
        $inst = new static();

        $inst->setCode(static::PSEUDO_ETAPE_FINALE);
        $inst->setLibelleActeur("C'est terminé : dépôt validé.");
        $inst->setLibelleAutres("C'est terminé : dépôt validé.");
        $inst->setOrdre(100000000000);
        $inst->setChemin(1);
        $inst->setRoute(null);
        $inst->setObligatoire(false);

        $inst->estPseudoEtapeFinale = true;

        return $inst;
    }
}