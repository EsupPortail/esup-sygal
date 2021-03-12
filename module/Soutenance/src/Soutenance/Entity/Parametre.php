<?php

namespace Soutenance\Entity;

class Parametre {

    const CODE_AVIS_DEADLINE                            = 'AVIS_DEADLINE';
    const CODE_JURY_SIZE_MIN                            = 'JURY_SIZE_MIN';
    const CODE_JURY_SIZE_MAX                            = 'JURY_SIZE_MAX';
    const CODE_JURY_RAPPORTEUR_SIZE_MIN                 = 'JURY_RAPPORTEUR_SIZE_MIN';
    const CODE_JURY_RANGA_RATIO_MIN                     = 'JURY_RANGA_RATIO_MIN';
    const CODE_JURY_EXTERIEUR_RATIO_MIN                 = 'JURY_EXTERIEUR_RATIO_MIN';
    const CODE_JURY_PARITE_RATIO_MIN                    = 'JURY_PARITE_RATIO_MIN';
//    const CODE_PROPOSITION_ORDRE_VALIDATION_ACTEUR      = 'PROPOSITION_ORDRE_VALIDATION_ACTEUR';
//    const CODE_PROPOSITION_ORDRE_VALIDATION_UR          = 'PROPOSITION_ORDRE_VALIDATION_UR';
//    const CODE_PROPOSITION_ORDRE_VALIDATION_ED          = 'PROPOSITION_ORDRE_VALIDATION_ED';
//    const CODE_PROPOSITION_ORDRE_VALIDATION_BDD         = 'PROPOSITION_ORDRE_VALIDATION_BDD';
//    const CODE_PROPOSITION_ORDRE_VALIDATION_PRESIDENT   = 'PROPOSITION_ORDRE_VALIDATION_PRESIDENT';
    const CODE_FORMULAIRE_DELOCALISATION                = 'FORMULAIRE_DELOCALISATION';
    const CODE_FORMULAIRE_DELEGUATION                   = 'FORMULAIRE_DELEGUATION';
    const CODE_FORMULAIRE_LABEL_EUROPEEN                = 'FORMULAIRE_LABEL_EUROPEEN';
    const CODE_FORMULAIRE_THESE_ANGLAIS                 = 'FORMULAIRE_THESE_ANGLAIS';
    const CODE_FORMULAIRE_CONFIDENTIALITE               = 'FORMULAIRE_CONFIDENTIALITE';

    const CODE_DIRECTEUR_INTERVENTION                   = 'PERIODE_INTERVENTION_DIRECTEUR';

    /** @var integer */
    private $id;
    /** @var string */
    private $code;
    /** @var string */
    private $libelle;
    /** @var string */
    private $valeur;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Parametre
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return Parametre
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * @param string $valeur
     * @return Parametre
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;
        return $this;
    }
}