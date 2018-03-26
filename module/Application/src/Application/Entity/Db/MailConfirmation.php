<?php

namespace Application\Entity\Db;

class MailConfirmation {

    const ENVOYER = 'E';
    const CONFIRMER = 'C';

    /** @var int $id */
    private $id;
    /** @var Individu $individu */
    protected $individu;
    /** @var string $email */
    protected $email;
    /** @var string $etat */
    protected $etat;
    /** @var string code */
    protected $code;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return MailConfirmation
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;
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
     * @return MailConfirmation
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param string $etat
     * @return MailConfirmation
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
        return $this;
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
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return boolean
     */
    public function isEnvoyer()
    {
        return $this->etat === MailConfirmation::ENVOYER;
    }

    /**
     * @return boolean
     */
    public function isConfirmer()
    {
        return $this->etat === MailConfirmation::CONFIRMER;
    }


}