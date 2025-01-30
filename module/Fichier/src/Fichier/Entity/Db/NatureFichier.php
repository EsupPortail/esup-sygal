<?php

namespace Fichier\Entity\Db;

/**
 * NatureFichier
 */
class NatureFichier
{
    const CODE_THESE_PDF = 'THESE_PDF';
    const CODE_FICHIER_NON_PDF = 'FICHIER_NON_PDF';

    // Fichiers divers
    const CODE_FORMATION_INTEGRITE_SCIENTIFIQUE = 'FORMATION_INTEGRITE_SCIENTIFIQUE';
    const CODE_AUTORISATION_SOUTENANCE = 'AUTORISATION_SOUTENANCE';
    const CODE_PV_SOUTENANCE = 'PV_SOUTENANCE';
    const CODE_DEMANDE_CONFIDENT = 'DEMANDE_CONFIDENT';
    const CODE_PROLONG_CONFIDENT = 'PROLONG_CONFIDENT';
    const CODE_CONV_MISE_EN_LIGNE = 'CONV_MISE_EN_LIGNE';
    const CODE_CONV_MISE_EN_LIGNE_AVENANT = 'CONV_MISE_EN_LIGNE_AVENANT';
    const CODE_PRE_RAPPORT_SOUTENANCE = 'PRE_RAPPORT_SOUTENANCE';
    const CODE_RAPPORT_SOUTENANCE = 'RAPPORT_SOUTENANCE';
    const CODE_RAPPORT_TECHNIQUE_SOUTENANCE = 'RAPPORT_TECHNIQUE_SOUTENANCE';
    const CODE_CONV_FORMATION_DOCTORALE = 'CONV_FORMATION_DOCTORALE';
    const CODE_CONV_FORMATION_DOCTORALE_AVENANT = 'CONV_FORMATION_DOCTORALE_AVENANT';
    const CODE_CHARTE_DOCTORAT = 'CHARTE_DOCTORAT';
    const CODE_CHARTE_DOCTORAT_AVENANT = 'CHARTE_DOCTORAT_AVENANT';
    const CODES_FICHIERS_DIVERS = [
        self::CODE_PV_SOUTENANCE,
        self::CODE_DEMANDE_CONFIDENT,
        self::CODE_PROLONG_CONFIDENT,
        self::CODE_CONV_MISE_EN_LIGNE,
        self::CODE_CONV_MISE_EN_LIGNE_AVENANT,
        self::CODE_PRE_RAPPORT_SOUTENANCE,
        self::CODE_RAPPORT_SOUTENANCE,
        self::CODE_RAPPORT_TECHNIQUE_SOUTENANCE,
        self::CODE_CONV_FORMATION_DOCTORALE,
        self::CODE_CONV_FORMATION_DOCTORALE_AVENANT,
        self::CODE_CHARTE_DOCTORAT,
        self::CODE_CHARTE_DOCTORAT_AVENANT,
    ];

    const CODE_COMMUNS = 'COMMUNS';

    const CODE_RAPPORT_ACTIVITE = 'RAPPORT_ACTIVITE';
    const CODE_RAPPORT_CSI = 'RAPPORT_CSI';
    const CODE_RAPPORT_MIPARCOURS = 'RAPPORT_MIPARCOURS';

    const CODE_SIGNATURE_CONVOCATION = 'SIGNATURE_CONVOCATION';
    const CODE_SIGNATURE_RAPPORT_ACTIVITE = 'SIGNATURE_RAPPORT_ACTIVITE';

    const CODE_JUSTIFICATIF_HDR = 'JUSTIFICATIF_HDR';
    const CODE_JUSTIFICATIF_EMERITAT = 'JUSTIFICATIF_EMERITAT';
    const CODE_JUSTIFICATIF_ETRANGER = 'JUSTIFICATIF_ETRANGER';
    const CODE_DELOCALISATION_SOUTENANCE = 'DELOCALISATION_SOUTENANCE';
    const CODE_DELEGUATION_SIGNATURE = 'DELEGUATION_SIGNATURE';
    const CODE_DEMANDE_LABEL = 'DEMANDE_LABEL_EUROPEEN';
    const CODE_LANGUE_ANGLAISE = 'DEMANDE_LANGUE_ANGLAISE';
    const CODE_AUTRES_JUSTIFICATIFS = 'AUTRES_JUSTIFICATIFS';

    const LABEL_FORMATION_INTEGRITE_SCIENTIFIQUE = 'Justificatif de suivi de la formation Intégrité scientifique';
    const LABEL_JUSTIFICATIF_HDR = "Justificatif d'habilitation à diriger des recherches";
    const LABEL_JUSTIFICATIF_EMERITAT = "Justificatif d'émeritat";
    const LABEL_JUSTIFICATIF_ETRANGER = "Justificatif dans le cas d'un membre du jury étranger";
    const LABEL_DELOCALISATION_SOUTENANCE = "Formulaire de délocalisation de soutenance";
    const LABEL_DELEGUATION_SIGNATURE = "Formulaire de délégation de signature du rapport de soutenance (visioconférence)";
    const LABEL_LANGUE_ANGLAISE = "Formulaire d'utilisation de la langue anglaise";
    const LABEL_DEMANDE_LABEL = "Formulaire de demande de label européen";
    const LABEL_DEMANDE_CONFIDENT = "Formulaire de demande de confidentialité";
    const LABEL_AUTRES_JUSTIFICATIFS = "Autres justificatifs concernant la soutenance";
    const LABEL_AUTORISATION_SOUTENANCE = 'Autorisation de soutenance';
    const LABEL_PV_SOUTENANCE = 'Procès-verbal de soutenance';
    const LABEL_RAPPORT_SOUTENANCE = 'Rapport de soutenance';
    const LABEL_RAPPORT_TECHNIQUE_SOUTENANCE = 'Rapport technique de soutenance';


    //Admission
    const CODE_ADMISSION_CHARTE_DOCTORAT = "ADMISSION_CHARTE_DOCTORAT";
    const CODE_ADMISSION_CHARTE_DOCTORAT_SIGNEE = "ADMISSION_CHARTE_DOCTORAT_SIGNEE";
    const CODE_ADMISSION_CONVENTION = "ADMISSION_CONVENTION";
    const CODE_ADMISSION_ATTESTATION_RESPONSABILITE_CIVILE = "ADMISSION_ATTESTATION_RESPONSABILITE_CIVILE";

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
     * @return static
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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Remplace les '_' par des '-' dans le code mis en caractères minuscules.
     *
     * @return string
     */
    public function getCodeToLowerAndDash(): string
    {
        return strtolower(str_replace('_', '-', $this->code));
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return static
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

    /**
     * @return bool
     */
    public function estThesePdf()
    {
        return $this->getCode() === NatureFichier::CODE_THESE_PDF;
    }

    /**
     * @return bool
     */
    public function estFichierNonPdf()
    {
        return $this->getCode() === NatureFichier::CODE_FICHIER_NON_PDF;
    }

    /**
     * @return bool
     */
    public function estRapportSoutenance()
    {
        return $this->getCode() === NatureFichier::CODE_RAPPORT_SOUTENANCE;
    }
}