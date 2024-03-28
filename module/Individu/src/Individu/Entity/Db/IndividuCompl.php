<?php

namespace Individu\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class IndividuCompl implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /** @var int $id */
    private $id;
    /** @var Individu $individu */
    private $individu;
    /** @var string $emailPro */
    private $emailPro;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Individu|null
     */
    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return IndividuCompl
     */
    public function setIndividu(Individu $individu): IndividuCompl
    {
        $this->individu = $individu;
        return $this;
    }

    /**
     * Retourne l'adresse électronique professionnelle/institutionnelle.
     *
     * @return string|null
     */
    public function getEmailPro(): ?string
    {
        return $this->emailPro;
    }

    /**
     * @param string $email
     * @return IndividuCompl
     */
    public function setEmailPro(string $email): IndividuCompl
    {
        $this->emailPro = $email;
        return $this;
    }
}