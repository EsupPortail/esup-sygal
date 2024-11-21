<?php

namespace Formation\Entity\Db;

use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class EnqueteReponse implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const NIVEAUX = [
        5 => "Très satisfait",
        4 => "Assez satisfait",
        3 => "Sans Avis",
        2 => "Peu satisfait",
        1 => "Pas satisfait",

    ];

    /** @var int */
    private $id;
    /** @var Inscription */
    private $inscription;
    /** @var EnqueteQuestion */
    private $question;
    /** @var int */
    private $niveau;
    /** @var string|null */
    private $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Inscription
     */
    public function getInscription(): Inscription
    {
        return $this->inscription;
    }

    /**
     * @param Inscription $inscription
     * @return EnqueteReponse
     */
    public function setInscription(Inscription $inscription): EnqueteReponse
    {
        $this->inscription = $inscription;
        return $this;
    }

    /**
     * @return EnqueteQuestion
     */
    public function getQuestion(): EnqueteQuestion
    {
        return $this->question;
    }

    /**
     * @param EnqueteQuestion $question
     * @return EnqueteReponse
     */
    public function setQuestion(EnqueteQuestion $question): EnqueteReponse
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    /**
     * @param int $niveau
     * @return EnqueteReponse
     */
    public function setNiveau(int $niveau): EnqueteReponse
    {
        $this->niveau = $niveau;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return EnqueteReponse
     */
    public function setDescription(?string $description): EnqueteReponse
    {
        $this->description = $description;
        return $this;
    }

}