<?php

namespace Formation\Entity\Db;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Structure;
use Application\Entity\Db\Utilisateur;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Session implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const MODALITE_PRESENTIEL   = 'P';
    const MODALITE_DISTANCIEL   = 'D';

    const ETAT_PREPARATION      = 'P';
    const ETAT_INSCRIPTION      = 'I';
    const ETAT_EN_COURS         = 'E';
    const ETAT_TERMINE          = 'T';
    const ETAT_CLOS_FINAL       = 'C';

    const TYPE_SPECIFIQUE       = 'S';
    const TYPE_TRANSVERSALE     = 'T';

    /** @var int */
    private $id;

    /** @var Formation|null */
    private $formation;

    /** Informations générale sur la session **************************************************************************/
    /** @var Etablissement|null */
    private $site;
    /** @var Utilisateur|null */
    private $responsable;
    /** @var string|null */
    private $modalite;
    /** @var string|null */
    private $etat;
    /** @var string */
    private $type;
    /** @var Structure|null */
    private $typeStructure;
    /** @var string|null */
    private $description;

    /** Liste des scéances ********************************************************************************************/
    /** @var Collection (Seance) */
    private $seances;

    /** Liste des insscriptions et listes associées *******************************************************************/
    /** @var int */
    private $tailleListePrincipale;
    /** @var int */
    private $tailleListeComplementaire;
    /** @var Collection (Inscription) */
    private $inscriptions;





    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Formation|null
     */
    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    /**
     * @param Formation|null $formation
     * @return Session
     */
    public function setFormation(?Formation $formation): Session
    {
        $this->formation = $formation;
        return $this;
    }

    /**
     * @return Etablissement|null
     */
    public function getSite(): ?Etablissement
    {
        return $this->site;
    }

    /**
     * @param Etablissement|null $site
     * @return Session
     */
    public function setSite(?Etablissement $site): Session
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return Utilisateur|null
     */
    public function getResponsable(): ?Utilisateur
    {
        return $this->responsable;
    }

    /**
     * @param Utilisateur|null $responsable
     * @return Session
     */
    public function setResponsable(?Utilisateur $responsable): Session
    {
        $this->responsable = $responsable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModalite(): ?string
    {
        return $this->modalite;
    }

    /**
     * @param string|null $modalite
     * @return Session
     */
    public function setModalite(?string $modalite): Session
    {
        $this->modalite = $modalite;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEtat(): ?string
    {
        return $this->etat;
    }

    /**
     * @param string|null $etat
     * @return Session
     */
    public function setEtat(?string $etat): Session
    {
        $this->etat = $etat;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Session
     */
    public function setType(string $type): Session
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Structure|null
     */
    public function getTypeStructure(): ?Structure
    {
        return $this->typeStructure;
    }

    /**
     * @param Structure|null $typeStructure
     * @return Session
     */
    public function setTypeStructure(?Structure $typeStructure): Session
    {
        $this->typeStructure = $typeStructure;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSeances() : Collection
    {
        return $this->seances;
    }

    /**
     * @return Collection
     */
    public function getInscriptions() : Collection
    {
        return $this->inscriptions;
    }

    /**
     * @param string|null $liste
     * @return Inscription[]
     */
    public function getInscriptionsByListe(?string $liste) : array
    {
        $result = array_filter($this->inscriptions->toArray(), function (Inscription $a) use ($liste) { return ($a->estNonHistorise() AND $a->getListe()===$liste);});
        return $result;
    }

    /**
     * @return int
     */
    public function getTailleListePrincipale(): int
    {
        return $this->tailleListePrincipale;
    }

    /**
     * @param int $tailleListePrincipale
     * @return Session
     */
    public function setTailleListePrincipale(int $tailleListePrincipale): Session
    {
        $this->tailleListePrincipale = $tailleListePrincipale;
        return $this;
    }

    /**
     * @return int
     */
    public function getTailleListeComplementaire(): int
    {
        return $this->tailleListeComplementaire;
    }

    /**
     * @param int $tailleListeComplementaire
     * @return Session
     */
    public function setTailleListeComplementaire(int $tailleListeComplementaire): Session
    {
        $this->tailleListeComplementaire = $tailleListeComplementaire;
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
     * @return Session
     */
    public function setDescription(?string $description): Session
    {
        $this->description = $description;
        return $this;
    }

    public function getDuree() : float
    {
        $somme = new DateTime('00:00');
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise()) {
                $debut = $seance->getDebut();
                $fin = $seance->getFin();
                $interval = $debut->diff($fin);
                $somme->add($interval);
            }
        }
        $interval = $somme->diff(new DateTime('00:00'));
        return ((float) $interval->format('%h')) + ((float) $interval->format('%i'))/60;
    }
}