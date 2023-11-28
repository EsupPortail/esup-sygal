<?php
namespace Admission\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class TypeValidation{

    const CODE_ATTESTATION_HONNEUR = 'ATTESTATION_HONNEUR';
    const CODE_VALIDATION_GESTIONNAIRE = 'VALIDATION_GESTIONNAIRE';
    const CODE_VALIDATION_DIRECTION_THESE = 'VALIDATION_DIRECTION_THESE';
    const CODE_VALIDATION_CO_DIRECTION_THESE = 'VALIDATION_CO_DIRECTION_THESE';
    const CODE_VALIDATION_UR = 'VALIDATION_UR';
    const CODE_VALIDATION_ED = 'VALIDATION_ED';
    const CODE_SIGNATURE_PRESIDENT = 'SIGNATURE_PRESIDENT';

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
