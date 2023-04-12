<?php

namespace UnicaenAvis\Entity\Db;

/**
 * AvisValeur
 */
class AvisValeur
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $valeur;

    /**
     * @var bool
     */
    private $valeurBool;

    /**
     * @var int
     */
    private $ordre;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $tags;

    /**
     * @var int
     */
    private $id;


    /**
     * Set code.
     *
     * @param string $code
     *
     * @return AvisValeur
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set valeur.
     *
     * @param string $valeur
     *
     * @return AvisValeur
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur.
     *
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * @return bool|null
     */
    public function getValeurBool(): ?bool
    {
        return $this->valeurBool;
    }

    /**
     * @param bool|null $valeurBool
     * @return self
     */
    public function setValeurBool(?bool $valeurBool): self
    {
        $this->valeurBool = $valeurBool;
        return $this;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return AvisValeur
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return AvisValeur
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getTags(): ?string
    {
        return $this->tags;
    }

    /**
     * @param string|null $tags
     * @return self
     */
    public function setTags(?string $tags = null): self
    {
        $this->tags = $tags;
        return $this;
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
