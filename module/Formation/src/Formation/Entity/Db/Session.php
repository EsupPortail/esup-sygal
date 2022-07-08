<?php

namespace Formation\Entity\Db;

use Doctorant\Entity\Db\Doctorant;
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
    const ETAT_CLOS_FINAL       = 'C';
    const ETATS = [
        self::ETAT_PREPARATION => self::ETAT_PREPARATION,
        self::ETAT_INSCRIPTION => self::ETAT_INSCRIPTION,
        self::ETAT_EN_COURS => self::ETAT_EN_COURS,
        self::ETAT_CLOS_FINAL => self::ETAT_CLOS_FINAL,
    ];

    const MODALITE_PRESENTIELLE = 'présentielle';
    const MODALITE_DISTANCIELLE = 'distancielle';

    private int $id;
    private ?int $index = null;
    private ?Formation $formation = null;

    private ?Individu $responsable = null;
    private ?Etat $etat = null;
    private ?string $description = null;

    private Collection $structuresComplemenaires;
    private Collection $seances;
    private Collection $formateurs;
    private Collection $inscriptions;

    private ?int $tailleListePrincipale = null;
    private ?int $tailleListeComplementaire = null;

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
     * @param Formation|null $formation
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
    public function getStructuresComplemenaires(): Collection
    {
        return $this->structuresComplemenaires;
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

    /** Pour les macros ********************************************************************************/

    /**
     * @return string
     */
    public function getSeancesAsTable() : string
    {
        $seances = $this->getSeances()->toArray();
        $seances = array_filter($seances, function(Seance $a) { return $a->estNonHistorise();});
        usort($seances, function (Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut(); });

        if (empty($seances)) return "Aucune séance d'associée à cette session de formation.";

        $texte  = '<table>';
        $texte .= '<thead><tr>';
        $texte .= '<th> Jour de la séance </th><th> Heure de début </th><th> Heure de fin </th><th> Lieu </th>';
        $texte .= '</tr></thead>';
        $texte .= '<tbody>';
        /** @var Seance $seance */
        foreach ($seances as $seance) {
            $texte .= '<tr>';
            $texte .= '<td>'.$seance->getDebut()->format('d/m/Y').'</td>';
            $texte .= '<td>'.$seance->getDebut()->format('H:i').'</td>';
            $texte .= '<td>'.$seance->getFin()->format('H:i').'</td>';
            $texte .= '<td>'.$seance->getLieu().'</td>';
            $texte .= '</tr>';
        }
        $texte .= '</tbody>';
        $texte .= '</table>';
        return $texte;
    }
}