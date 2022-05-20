<?php

namespace Individu\Entity\Db;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\UniteRecherche;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class IndividuCompl implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int $id */
    private $id;
    /** @var Individu $individu */
    private $individu;
    /** @var string $email */
    private $email;
    /** @var \Application\Entity\Db\Etablissement $etablissement */
    private $etablissement;
    /** @var \Application\Entity\Db\UniteRecherche $uniteRecherche */
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
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return IndividuCompl
     */
    public function setEmail(string $email): IndividuCompl
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return \Application\Entity\Db\Etablissement|null
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
     * @return \Application\Entity\Db\UniteRecherche|null
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