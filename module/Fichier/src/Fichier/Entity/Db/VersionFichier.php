<?php

namespace Fichier\Entity\Db;

/**
 * VersionFichier
 */
class VersionFichier
{
    /**
     * Constantes définissant les codes de "version" du fichier.
     *
     * - 'VO' = Version originale
     * - 'VA' = Version d'archivage, destinée à être déposée sur la plateforme STAR
     * - 'VD' = Version de diffusion, expurgée des contenus confidentiels notamment
     * - 'VOC' = Version originale corrigée
     * - 'VAC' = Version d'archivage corrigée, destinée à être déposée sur la plateforme STAR
     * - 'VDC' = Version de diffusion corrigée, expurgée des contenus confidentiels notamment
     */
    const CODE_ORIG         = 'VO';
    const CODE_ARCHI        = 'VA';
    const CODE_DIFF         = 'VD';
    const CODE_ORIG_CORR    = 'VOC';
    const CODE_ARCHI_CORR   = 'VAC';
    const CODE_DIFF_CORR    = 'VDC';

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
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * @return string
     */
    public function toString()
    {
        $pieces = [];

        switch (true) {
            case $this->estVersionOriginale():
                $pieces[] = "la version originale";
                break;
            case $this->estVersionDiffusion():
                $pieces[] = "la version de diffusion";
                break;
            case $this->estVersionArchivage():
                $pieces[] = "la version d'archivage";
                break;
            default:
                break;
        }

        if ($this->estVersionCorrigee()) {
            $pieces[] = "corrigée";
        }

        return join(" ", $pieces);
    }

    /**
     * Indique si cette version est l'originale, corrigée ou non.
     *
     * @return bool
     */
    public function estVersionOriginale()
    {
        return in_array($this->getCode(), [
            self::CODE_ORIG,
            self::CODE_ORIG_CORR,
        ]);
    }

    /**
     * Indique si cette version est la version de diffusion (expurgée), corrigée ou non.
     *
     * @return bool
     */
    public function estVersionDiffusion()
    {
        return in_array($this->getCode(), [
            self::CODE_DIFF,
            self::CODE_DIFF_CORR,
        ]);
    }

    /**
     * Indique si cette version est celle d'archivage, corrigée ou non.
     *
     * @return bool
     */
    public function estVersionArchivage()
    {
        return in_array($this->getCode(), [
            self::CODE_ARCHI,
            self::CODE_ARCHI_CORR,
        ]);
    }

    /**
     * Indique si le code spécifié correspond à une version corrigée ou non.
     *
     * @param string $code Ex: VersionFichier::CODE_ORIG
     * @return bool
     */
    static public function codeEstVersionCorrigee($code)
    {
        return in_array($code, [
            self::CODE_ORIG_CORR,
            self::CODE_ARCHI_CORR,
            self::CODE_DIFF_CORR,
        ]);
    }

    /**
     * Indique si cette version est corrigée ou non.
     *
     * @return bool
     */
    public function estVersionCorrigee()
    {
        return self::codeEstVersionCorrigee($this->getCode());
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return VersionFichier
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
     * @return VersionFichier
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

