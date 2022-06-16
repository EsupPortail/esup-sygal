<?php

namespace Fichier\Entity\Db;

/**
 * NatureFichier
 */
class NatureFichier
{
    const CODE_THESE_PDF = 'THESE_PDF';
    const CODE_FICHIER_NON_PDF = 'FICHIER_NON_PDF';

    const CODE_PV_SOUTENANCE = 'PV_SOUTENANCE';
    const CODE_RAPPORT_SOUTENANCE = 'RAPPORT_SOUTENANCE';
    const CODE_RAPPORT_ACTIVITE = 'RAPPORT_ACTIVITE';
    const CODE_PRE_RAPPORT_SOUTENANCE = 'PRE_RAPPORT_SOUTENANCE';
    const CODE_DEMANDE_CONFIDENT = 'DEMANDE_CONFIDENT';
    const CODE_PROLONG_CONFIDENT = 'PROLONG_CONFIDENT';
    const CODE_CONV_MISE_EN_LIGNE = 'CONV_MISE_EN_LIGNE';
    const CODE_AVENANT_CONV_MISE_EN_LIGNE = 'AVENANT_CONV_MISE_EN_LIGNE';
    const CODE_COMMUNS = 'COMMUNS';

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

    const LABEL_JUSTIFICATIF_HDR = "Justificatif d'habilitation à diriger des recherches";
    const LABEL_JUSTIFICATIF_EMERITAT = "Justificatif d'émeritat";
    const LABEL_JUSTIFICATIF_ETRANGER = "Justificatif dans le cas d'un membre du jury étranger";
    const LABEL_DELOCALISATION_SOUTENANCE = "Formulaire de délocalisation de soutenance";
    const LABEL_DELEGUATION_SIGNATURE = "Formulaire de délégation de signature du rapport de soutenance (visioconférence)";
    const LABEL_LANGUE_ANGLAISE = "Formulaire d'utilisation de la langue anglaise";
    const LABEL_DEMANDE_LABEL = "Formulaire de demande de label européen";
    const LABEL_DEMANDE_CONFIDENT = "Formulaire de demande de confidentialité";
    const LABEL_AUTRES_JUSTIFICATIFS = "Autres justificatifs concernant la soutenance";

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
     * @param string $code
     * @return string
     */
    static public function toRoute($code)
    {
        // ex: 'AVENANT_CONV_MISE_EN_LIGNE' => "avenant-conv-mise-en-ligne"
        return strtolower(str_replace('_', '-', $code));
    }

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
    public function getCode()
    {
        return $this->code;
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