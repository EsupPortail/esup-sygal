<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation\Entity\Db;

use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Inscription implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const LISTE_PRINCIPALE = 'P';
    const LISTE_COMPLEMENTAIRE = 'C';
    const LISTES = [
        self::LISTE_PRINCIPALE => "Principale",
        self::LISTE_COMPLEMENTAIRE => "Complémentaire",
    ];

    private int $id;
    private ?Session $session = null;
    private ?Doctorant $doctorant = null;
    private ?string $liste = null;
    private ?string $description = null;
    private ?Collection $presences = null;
    private ?DateTime $validationEnquete = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return Inscription
     */
    public function setSession(Session $session): Inscription
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return Doctorant|null
     */
    public function getDoctorant(): ?Doctorant
    {
        return $this->doctorant;
    }

    /**
     * @param Doctorant $doctorant
     * @return Inscription
     */
    public function setDoctorant(Doctorant $doctorant): Inscription
    {
        $this->doctorant = $doctorant;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getListe(): ?string
    {
        return $this->liste;
    }

    /**
     * @return bool
     */
    public function isListePrincipale() : bool
    {
        return ($this->liste === Inscription::LISTE_PRINCIPALE);
    }

    /**
     * @return bool
     */
    public function isListeComplementaire() : bool
    {
        return ($this->liste === Inscription::LISTE_COMPLEMENTAIRE);
    }

    /**
     * @param string|null $liste
     * @return Inscription
     */
    public function setListe(?string $liste): Inscription
    {
        $this->liste = $liste;
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
     * @return Inscription
     */
    public function setDescription(?string $description): Inscription
    {
        $this->description = $description;
        return $this;
    }

    public function computeDureePresence() : float
    {
        $duree = 0;
        /** @var Presence $presence */
        foreach ($this->presences as $presence) {
            if ($presence->estNonHistorise() AND $presence->isPresent()) {
                $duree += $presence->getSeance()->getDuree();
            }
        }
        return $duree;
    }

    /**
     * @return DateTime|null
     */
    public function getValidationEnquete(): ?DateTime
    {
        return $this->validationEnquete;
    }

    /**
     * @param DateTime|null $validationEnquete
     */
    public function setValidationEnquete(?DateTime $validationEnquete): void
    {
        $this->validationEnquete = $validationEnquete;
    }

    public function getPositionListeComplementaire() : int
    {
        if ($this->getListe() !== Inscription::LISTE_COMPLEMENTAIRE OR $this->estHistorise()) return -1;

        $liste = $this->session->getListeComplementaire();
        usort($liste, function(Inscription $a, Inscription $b) { return $a->getHistoCreation() > $b->getHistoCreation(); });

        for ($i = 0 ; $i < count($liste) ; $i++) {
            if ($liste[$i] === $this) return ($i+1);
        }
        return -1;
    }


}