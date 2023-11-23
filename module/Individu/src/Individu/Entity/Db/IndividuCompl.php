<?php

namespace Individu\Entity\Db;

use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class IndividuCompl implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int $id */
    private $id;
    /** @var Individu $individu */
    private $individu;
    /** @var string $emailPro */
    private $emailPro;
    /** @var Etablissement $etablissement */
    private $etablissement;
    /** @var UniteRecherche $uniteRecherche */
    private $uniteRecherche;

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

    /**
     * Retourne l'éventuel établissement lié.
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement|null $etablissement
     */
    public function setEtablissement(?Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    /**
     * Retourne l'éventuelle UR liée.
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        return $this->uniteRecherche;
    }

    /**
     * @param UniteRecherche|null $uniteRecherche
     */
    public function setUniteRecherche(?UniteRecherche $uniteRecherche): void
    {
        $this->uniteRecherche = $uniteRecherche;
    }

}