<?php

namespace Application\Entity\Db;

class MailConfirmation {

    const ENVOYE = 'E';
    const CONFIRME = 'C';

    /** @var int $id */
    private $id;
    /** @var \Individu\Entity\Db\Individu $individu */
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
     * @return \Individu\Entity\Db\Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param \Individu\Entity\Db\Individu $individu
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
    public function estConfirme(): bool
    {
        return $this->etat === MailConfirmation::CONFIRME;
    }

    /**
     * @return boolean
     */
    public function estEnvoye(): bool
    {
        return $this->etat === MailConfirmation::ENVOYE;
    }
}