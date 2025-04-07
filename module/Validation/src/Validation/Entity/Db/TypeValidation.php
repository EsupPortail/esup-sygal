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

    public const CODE_RDV_BU                       = 'RDV_BU';
    public const CODE_DEPOT_THESE_CORRIGEE         = 'DEPOT_THESE_CORRIGEE';
    public const CODE_CORRECTION_THESE             = 'CORRECTION_THESE';
    public const CODE_VERSION_PAPIER_CORRIGEE      = 'VERSION_PAPIER_CORRIGEE';
    public const CODE_PAGE_DE_COUVERTURE           = 'PAGE_DE_COUVERTURE';

    public const CODE_PROPOSITION_SOUTENANCE        = 'PROPOSITION_SOUTENANCE';
    public const CODE_ENGAGEMENT_IMPARTIALITE       = 'ENGAGEMENT_IMPARTIALITE';
    public const CODE_REFUS_ENGAGEMENT_IMPARTIALITE = 'REFUS_ENGAGEMENT_IMPARTIALITE';
    public const CODE_VALIDATION_PROPOSITION_ED     = 'VALIDATION_PROPOSITION_ED';
    public const CODE_VALIDATION_PROPOSITION_UR     = 'VALIDATION_PROPOSITION_UR';
    public const CODE_VALIDATION_PROPOSITION_BDD    = 'VALIDATION_PROPOSITION_BDD';
    public const CODE_AVIS_SOUTENANCE               = 'AVIS_SOUTENANCE';

    /** @deprecated Validation du rapport non dématérialisé (ancienne version du module Rapport d'activité) */
    public const CODE_RAPPORT_ACTIVITE_AUTO         = 'RAPPORT_ACTIVITE_AUTO';
    public const CODE_RAPPORT_ACTIVITE_DOCTORANT    = 'RAPPORT_ACTIVITE_DOCTORANT';

    public const CODE_RAPPORT_CSI                   = 'RAPPORT_CSI';
    public const CODE_RAPPORT_MIPARCOURS            = 'RAPPORT_MIPARCOURS';

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