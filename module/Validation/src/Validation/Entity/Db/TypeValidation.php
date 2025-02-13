<?php

namespace Validation\Entity\Db;

use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

/**
 * TypeValidation
 */
class TypeValidation implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const CODE_RDV_BU                       = 'RDV_BU';
    const CODE_DEPOT_THESE_CORRIGEE         = 'DEPOT_THESE_CORRIGEE';
    const CODE_CORRECTION_THESE             = 'CORRECTION_THESE';
    const CODE_VERSION_PAPIER_CORRIGEE      = 'VERSION_PAPIER_CORRIGEE';
    const CODE_PAGE_DE_COUVERTURE           = 'PAGE_DE_COUVERTURE';

    const CODE_PROPOSITION_SOUTENANCE        = 'PROPOSITION_SOUTENANCE';
    const CODE_ENGAGEMENT_IMPARTIALITE       = 'ENGAGEMENT_IMPARTIALITE';
    const CODE_REFUS_ENGAGEMENT_IMPARTIALITE = 'REFUS_ENGAGEMENT_IMPARTIALITE';
    const CODE_VALIDATION_PROPOSITION_ED     = 'VALIDATION_PROPOSITION_ED';
    const CODE_VALIDATION_PROPOSITION_UR     = 'VALIDATION_PROPOSITION_UR';
    const CODE_VALIDATION_PROPOSITION_BDD    = 'VALIDATION_PROPOSITION_BDD';
    const CODE_AVIS_SOUTENANCE               = 'AVIS_SOUTENANCE';

    /** @deprecated Validation du rapport non dématérialisé (ancienne version du module Rapport d'activité) */
    const CODE_RAPPORT_ACTIVITE_AUTO         = 'RAPPORT_ACTIVITE_AUTO';
    const CODE_RAPPORT_ACTIVITE_DOCTORANT    = 'RAPPORT_ACTIVITE_DOCTORANT';

    const CODE_RAPPORT_CSI                   = 'RAPPORT_CSI';
    const CODE_RAPPORT_MIPARCOURS            = 'RAPPORT_MIPARCOURS';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $libelle;

    /**
     * @var integer
     */
    private $id;

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return TypeValidation
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return TypeValidation
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
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

}