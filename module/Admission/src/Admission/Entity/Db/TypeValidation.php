<?php
namespace Admission\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class TypeValidation{

    const CODE_ATTESTATION_HONNEUR_CHARTE_DOCTORALE = 'ATTESTATION_HONNEUR_CHARTE_DOCTORALE';
    const CODE_ATTESTATION_HONNEUR = 'ATTESTATION_HONNEUR';
    const CODE_VALIDATION_GESTIONNAIRE = 'VALIDATION_GESTIONNAIRE';
    const CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE = 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_THESE';
    const CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE = 'VALIDATION_CONVENTION_FORMATION_DOCT_CODIR_THESE';
    const CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR = 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_UR';
    const CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED = 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_ED';
    const CODE_SIGNATURE_PRESIDENT = 'SIGNATURE_PRESIDENT';

    const CODE_VALIDATIONS_CONVENTION_FORMATION_DOCTORALE = array(
        self::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_THESE,
        self::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_CODIR_THESE,
        self::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_UR,
        self::CODE_VALIDATION_CONVENTION_FORMATION_DOCTORALE_DIR_ED,
    );

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $libelle;

    /**
     * @var int
     */
    private $id;

    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * Set code.
     *
     * @param string|null $code
     *
     * @return TypeValidation
     */
    public function setCode($code = null)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle.
     *
     * @param string|null $libelle
     *
     * @return TypeValidation
     */
    public function setLibelle($libelle = null)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string|null
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
