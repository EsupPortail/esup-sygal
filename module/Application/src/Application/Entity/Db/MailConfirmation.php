<?php

namespace Application\Entity\Db;

use Individu\Entity\Db\Individu;

class MailConfirmation
{
    const ENVOYE = 'E';
    const CONFIRME = 'C';

    private $id;
    /** @var \Individu\Entity\Db\Individu $individu */
    protected $individu;
    /** @var string $email */
    protected $email;
    /** @var string $etat */
    protected $etat;
    /** @var string code */
    protected $code;
    protected bool $refusListeDiff = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIndividu(): Individu
    {
        return $this->individu;
    }

    public function setIndividu(Individu $individu): self
    {
        $this->individu = $individu;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRefusListeDiff(): bool
    {
        return $this->refusListeDiff;
    }

    /**
     * @param bool|null $refusListeDiff
     * @return self
     */
    public function setRefusListeDiff(?bool $refusListeDiff = true): self
    {
        $this->refusListeDiff = $refusListeDiff;
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