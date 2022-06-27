<?php

namespace Formation\Entity\Db;

use Doctorant\Entity\Db\Doctorant;
use Structure\Entity\Db\Etablissement;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Traits\HasModaliteTrait;
use Formation\Entity\Db\Traits\HasSiteTrait;
use Formation\Entity\Db\Traits\HasTypeTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Session implements HistoriqueAwareInterface,
    HasSiteInterface, HasModaliteInterface, HasTypeInterface {
    use HistoriqueAwareTrait;
    use HasSiteTrait;
    use HasModaliteTrait;
    use HasTypeTrait;

    const ETAT_PREPARATION      = 'P';
    const ETAT_INSCRIPTION      = 'O';
    const ETAT_EN_COURS         = 'E';
    const ETAT_TERMINE          = 'T';
    const ETAT_CLOS_FINAL       = 'C';

    const MODALITE_PRESENTIELLE = 'Présentielle';
    const MODALITE_DISTANCIELLE = 'Distancielle';

    /** @var int */
    private $id;

    /** @var int */
    private $index;
    /** @var Formation|null */
    private $formation;

    /** Informations générale sur la session **************************************************************************/
    /** @var Etablissement|null */
    private $site;
    /** @var Utilisateur|null */
    private $responsable;
    /** @var Etat|null */
    private $etat;
    /** @var string|null */
    private $description;

    /** Liste des scéances ********************************************************************************************/
    /** @var Collection (Seance) */
    private $seances;
    /** Liste des formateurs ******************************************************************************************/
    /** @var Collection (Formateur) */
    private $formateurs;

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
     * @return int|null
     */
    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * @param int $index
     * @return Session
     */
    public function setIndex(int $index): Session
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @return Formation|null
     */
    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    /**
     * @param Formation|null $module
     * @return Session
     */
    public function setFormation(?Formation $formation): Session
    {
        $this->formation = $formation;
        return $this;
    }

    /**
     * @return Utilisateur|null
     */
    public function getResponsable(): ?Individu
    {
        return $this->responsable;
    }

    /**
     * @param Individu|null $responsable
     * @return Session
     */
    public function setResponsable(?Individu $responsable): Session
    {
        $this->responsable = $responsable;
        return $this;
    }

    /**
     * @return Etat|null
     */
    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    /**
     * @param Etat|null $etat
     * @return Session
     */
    public function setEtat(?Etat $etat): Session
    {
        $this->etat = $etat;
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
    public function getFormateurs() : Collection
    {
        return $this->formateurs;
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
     * @return int|null
     */
    public function getTailleListePrincipale(): ?int
    {
        return $this->tailleListePrincipale;
    }

    /**
     * @param int|null $tailleListePrincipale
     * @return Session
     */
    public function setTailleListePrincipale(?int $tailleListePrincipale): Session
    {
        $this->tailleListePrincipale = $tailleListePrincipale;
        return $this;
    }

    public function getListePrincipale() : array
    {
        $inscriptions = array_filter($this->getInscriptions()->toArray(), function (Inscription $a) { return $a->estNonHistorise() AND $a->getListe() === Inscription::LISTE_PRINCIPALE; });
        return $inscriptions;
    }
    /**
     * @return int|null
     */
    public function getTailleListeComplementaire(): ?int
    {
        return $this->tailleListeComplementaire;
    }

    /**
     * @param int|null $tailleListeComplementaire
     * @return Session
     */
    public function setTailleListeComplementaire(?int $tailleListeComplementaire): Session
    {
        $this->tailleListeComplementaire = $tailleListeComplementaire;
        return $this;
    }

    public function getListeComplementaire() : array
    {
        $inscriptions = array_filter($this->getInscriptions()->toArray(), function (Inscription $a) { return $a->estNonHistorise() AND $a->getListe() === Inscription::LISTE_COMPLEMENTAIRE; });
        return $inscriptions;
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

    public function estTerminee() : bool
    {
        $maintenant = new DateTime();
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise() AND $seance->getFin() > $maintenant) return false;
        }
        return true;
    }

    public function getDateDebut() : ?DateTime
    {
        $debut = null;
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise()) {
                if ($debut === null or $seance->getDebut() < $debut) $debut = $seance->getDebut();
            }
        }
        return $debut;
    }

    public function getDateFin() : ?DateTime
    {
        $fin = null;
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise()) {
                if ($fin === null or $seance->getFin() > $fin) $fin = $seance->getFin();
            }
        }
        return $fin;
    }

    public function getLimiteInscription() : ?DateTime
    {
        $debut = $this->getDateDebut();
        if ($debut === null) return null;

        $limite = DateTime::createFromFormat('d/m/Y', $debut->format('d/m/y'));
        $limite->sub(new DateInterval('P14D'));
        return $limite;
    }

    /**
     * @param Doctorant $doctorant
     * @return bool
     */
    public function estInscrit(Doctorant $doctorant) : bool
    {
        /** @var Inscription $inscription */
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->estNonHistorise() AND $inscription->getDoctorant() === $doctorant) return true;
        }
        return false;
    }
}