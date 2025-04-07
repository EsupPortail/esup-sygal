<?php

namespace Fichier\Entity\Db;

use Application\Search\Filter\SearchFilterValueInterface;

/**
 * NatureFichier
 */
class NatureFichier implements SearchFilterValueInterface
{
    public const CODE_THESE_PDF = 'THESE_PDF';
    public const CODE_HDR_PDF = 'HDR_PDF';
    public const CODE_FICHIER_NON_PDF = 'FICHIER_NON_PDF';

    // Fichiers divers
    public const CODE_FORMATION_INTEGRITE_SCIENTIFIQUE = 'FORMATION_INTEGRITE_SCIENTIFIQUE';
    public const CODE_AUTORISATION_SOUTENANCE = 'AUTORISATION_SOUTENANCE';
    public const CODE_PV_SOUTENANCE = 'PV_SOUTENANCE';
    public const CODE_DEMANDE_CONFIDENT = 'DEMANDE_CONFIDENT';
    public const CODE_PROLONG_CONFIDENT = 'PROLONG_CONFIDENT';
    public const CODE_CONV_MISE_EN_LIGNE = 'CONV_MISE_EN_LIGNE';
    public const CODE_CONV_MISE_EN_LIGNE_AVENANT = 'CONV_MISE_EN_LIGNE_AVENANT';
    public const CODE_PRE_RAPPORT_SOUTENANCE = 'PRE_RAPPORT_SOUTENANCE';
    public const CODE_RAPPORT_SOUTENANCE = 'RAPPORT_SOUTENANCE';
    public const CODE_RAPPORT_TECHNIQUE_SOUTENANCE = 'RAPPORT_TECHNIQUE_SOUTENANCE';
    public const CODE_CONV_FORMATION_DOCTORALE = 'CONV_FORMATION_DOCTORALE';
    public const CODE_CONV_FORMATION_DOCTORALE_AVENANT = 'CONV_FORMATION_DOCTORALE_AVENANT';
    public const CODE_CHARTE_DOCTORAT = 'CHARTE_DOCTORAT';
    public const CODE_CHARTE_DOCTORAT_AVENANT = 'CHARTE_DOCTORAT_AVENANT';
    public const CODES_FICHIERS_DIVERS = [
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

    public const CODE_COMMUNS = 'COMMUNS';

    public const CODE_RAPPORT_ACTIVITE = 'RAPPORT_ACTIVITE';
    public const CODE_RAPPORT_CSI = 'RAPPORT_CSI';
    public const CODE_RAPPORT_MIPARCOURS = 'RAPPORT_MIPARCOURS';

    public const CODE_SIGNATURE_CONVOCATION = 'SIGNATURE_CONVOCATION';
    public const CODE_SIGNATURE_RAPPORT_ACTIVITE = 'SIGNATURE_RAPPORT_ACTIVITE';

    public const CODE_JUSTIFICATIF_HDR = 'JUSTIFICATIF_HDR';
    public const CODE_JUSTIFICATIF_EMERITAT = 'JUSTIFICATIF_EMERITAT';
    public const CODE_JUSTIFICATIF_ETRANGER = 'JUSTIFICATIF_ETRANGER';
    public const CODE_DELOCALISATION_SOUTENANCE = 'DELOCALISATION_SOUTENANCE';
    public const CODE_DELEGUATION_SIGNATURE = 'DELEGUATION_SIGNATURE';
    public const CODE_DEMANDE_LABEL = 'DEMANDE_LABEL_EUROPEEN';
    public const CODE_LANGUE_ANGLAISE = 'DEMANDE_LANGUE_ANGLAISE';
    public const CODE_AUTRES_JUSTIFICATIFS = 'AUTRES_JUSTIFICATIFS';

    public const LABEL_FORMATION_INTEGRITE_SCIENTIFIQUE = 'Justificatif de suivi de la formation Intégrité scientifique';
    public const LABEL_JUSTIFICATIF_HDR = "Justificatif d'habilitation à diriger des recherches";
    public const LABEL_JUSTIFICATIF_EMERITAT = "Justificatif d'émeritat";
    public const LABEL_JUSTIFICATIF_ETRANGER = "Justificatif dans le cas d'un membre du jury étranger";
    public const LABEL_DELOCALISATION_SOUTENANCE = "Formulaire de délocalisation de soutenance";
    public const LABEL_DELEGUATION_SIGNATURE = "Formulaire de délégation de signature du rapport de soutenance (visioconférence)";
    public const LABEL_LANGUE_ANGLAISE = "Formulaire d'utilisation de la langue anglaise";
    public const LABEL_DEMANDE_LABEL = "Formulaire de demande de label européen";
    public const LABEL_DEMANDE_CONFIDENT = "Formulaire de demande de confidentialité";
    public const LABEL_AUTRES_JUSTIFICATIFS = "Autres justificatifs concernant la soutenance";
    public const LABEL_AUTORISATION_SOUTENANCE = 'Autorisation de soutenance';
    public const LABEL_PV_SOUTENANCE = 'Procès-verbal de soutenance';
    public const LABEL_RAPPORT_SOUTENANCE = 'Rapport de soutenance';
    public const LABEL_RAPPORT_TECHNIQUE_SOUTENANCE = 'Rapport technique de soutenance';


    //Admission
    public const CODE_ADMISSION_CHARTE_DOCTORAT = "ADMISSION_CHARTE_DOCTORAT";
    public const CODE_ADMISSION_CHARTE_DOCTORAT_SIGNEE = "ADMISSION_CHARTE_DOCTORAT_SIGNEE";
    public const CODE_ADMISSION_CONVENTION = "ADMISSION_CONVENTION";
    public const CODE_ADMISSION_CONVENTION_SIGNEE = "ADMISSION_CONVENTION_SIGNEE";

    public const CODE_ADMISSION_ATTESTATION_RESPONSABILITE_CIVILE = "ADMISSION_ATTESTATION_RESPONSABILITE_CIVILE";

    //HDR
    public const CODE_DELOCALISATION_SOUTENANCE_HDR = 'DELOCALISATION_SOUTENANCE_HDR';
    public const CODE_DELEGATION_SIGNATURE_HDR = 'DELEGATION_SIGNATURE_HDR';
    public const CODE_DEMANDE_CONFIDENT_HDR = 'DEMANDE_CONFIDENT_HDR';
    public const CODE_LANGUE_ANGLAISE_HDR = 'DEMANDE_LANGUE_ANGLAISE_HDR';
    public const LABEL_DEMANDE_CONFIDENT_HDR = "Formulaire de demande de confidentialité pour une HDR";
    public const LABEL_DELOCALISATION_SOUTENANCE_HDR = "Formulaire de délocalisation de soutenance HDR";
    public const LABEL_DELEGUATION_SIGNATURE_HDR = "Formulaire de délégation de signature du rapport de soutenance (visioconférence) pour une HDR";
    public const LABEL_LANGUE_ANGLAISE_HDR = "Formulaire d'utilisation de la langue anglaise pour une HDR";


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

    public function createSearchFilterValueOption(): array
    {
        return ['value' => $this->getCode(), 'label' => $this->getCode(), 'subtext' => $this->getLibelle()];
    }
}