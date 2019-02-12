<?php

namespace Application\Entity\Db;

/**
 * Parametre
 */
class Parametre
{
    // todo: cette valeur n'existe pas dans la table !
    const APP_UTILISATEUR_ID = 'APP_UTILISATEUR_ID';

    const ID__SOURCE_CODE_ETAB_COMMUNAUTE = 'SOURCE_CODE_ETAB_COMMUNAUTE';

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $valeur;

    /**
     * @var string
     */
    protected $id;


    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Parametre
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     *
     * @return Parametre
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }
}