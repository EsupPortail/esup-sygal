<?php

namespace Application\Entity\Db;

class CreationUtilisateurInfos {

    /** @var string */
    protected $civilite;
    /** @var string */
    protected $nomUsuel;
    /** @var string */
    protected $nomPatronymique;
    /** @var string */
    protected $prenom;
    /** @var string */
    protected $email;
    /** @var string */
    protected $password;

    /**
     * @return string
     */
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * @param string $civilite
     * @return CreationUtilisateurInfos
     */
    public function setCivilite($civilite)
    {
        $this->civilite = $civilite;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomUsuel()
    {
        return $this->nomUsuel;
    }

    /**
     * @param string $nomUsuel
     * @return CreationUtilisateurInfos
     */
    public function setNomUsuel($nomUsuel)
    {
        $this->nomUsuel = $nomUsuel;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomPatronymique()
    {
        return $this->nomPatronymique;
    }

    /**
     * @param string $nomPatronymique
     * @return CreationUtilisateurInfos
     */
    public function setNomPatronymique($nomPatronymique)
    {
        $this->nomPatronymique = $nomPatronymique;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     * @return CreationUtilisateurInfos
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return CreationUtilisateurInfos
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return CreationUtilisateurInfos
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }





}