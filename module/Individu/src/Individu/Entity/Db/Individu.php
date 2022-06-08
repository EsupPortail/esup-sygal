<?php

namespace Individu\Entity\Db;

use Application\Constants;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\Pays;
use Application\Entity\Db\Role;
use Application\Entity\Db\UniteRecherche;
use Application\Filter\NomCompletFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Individu
 */
class Individu implements HistoriqueAwareInterface, SourceAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    const CIVILITE_M = 'M.';
    const CIVILITE_MME = 'Mme';

    const TYPE_ACTEUR = 'acteur';

    /**
     * Identifiant qui correspond en fait au :
     * - supannEmpId (pour les acteurs) ou au
     * - supannEtuId (pour les doctorants)
     * que nous fournit l'authentification shibboleth.
     *
     * Si cet identifiant est null, cela signifie que l'individu ne pourra pas se connecter.
     * Sa valeur résulte de l'import de données d'une source.
     * NB: Sa valeur proprement dite n'a pas vocation à être utilisée, c'est simplement sa nullité ou non qui
     * nous intéresse.
     *
     * todo: se contenter d'une valeur booléenne + renommer en qqchose de moins lié à supann
     *
     * @var string
     */
    protected $supannId;

    /**
     * @var string
     */
    protected $civilite;

    /**
     * @var string
     */
    protected $nationalite;

    /**
     * @var \DateTime
     */
    protected $dateNaissance;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $nomPatronymique;

    /**
     * @var string
     */
    protected $nomUsuel;

    /**
     * @var string
     */
    protected $prenom1;

    /**
     * @var string
     */
    protected $prenom2;

    /**
     * @var string
     */
    protected $prenom3;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var ArrayCollection
     */
    private $roles;

    /**
     * @var ArrayCollection (mailContact)
     */
    private $mailsConfirmations;

    /**
     * @var ArrayCollection
     */
    private $utilisateurs;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Etablissement|null
     */
    private $etablissement;

    /**
     * @var \Application\Entity\Db\Pays|null
     */
    private $paysNationalite = null;

    /**
     * @var ArrayCollection|IndividuCompl[]
     */
    private $complements;

    /**
     * Individu constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->mailsConfirmations = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
        $this->complements = new ArrayCollection();
    }

    /**
     * Get histoModification
     *
     * @return \DateTime
     */
    public function getHistoModification()
    {
        return $this->histoModification ?: $this->getHistoCreation();
    }

    /**
     * @return string
     * @see supannId
     */
    public function getSupannId()
    {
        return $this->supannId;
    }

    /**
     * @param string $supannId
     * @return self
     */
    public function setSupannId($supannId)
    {
        $this->supannId = $supannId;

        return $this;
    }

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return self
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance
     *
     * @return \DateTime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @return string
     * @deprecated Utiliser {@see getPaysNationalite()}
     */
    public function getNationalite()
    {
        return $this->nationalite;
    }

    /**
     * @param string $nationalite
     * @return Individu
     * @deprecated Utiliser {@see setPaysNationalite()}
     */
    public function setNationalite($nationalite)
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne l'adresse mail la plus susceptible d'être "fiable" parmi toutes celles disponibles.
     *
     * @return string|null
     */
    public function getEmailBestOf(): ?string
    {
        return $this->getEmailContact() ?: $this->getEmail() ?: $this->getEmailUtilisateur();
    }

    /**
     * Retourne l'adresse mail pour cet individu.
     *
     * **Conseil : voir aussi {@see getEmailBestOf()}.**
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        $complement = $this->getComplement();
        if ($complement AND !$complement->estHistorise() AND $complement->getEmail() !== null) return $complement->getEmail();
        return $this->email;
    }

    /**
     * Retourne l'adresse mail de contact, renseignée par le doctorant lui-même.
     *
     * **Conseil : voir aussi {@see getEmailBestOf()}.**
     *
     * @return string|null
     */
    public function getEmailContact(): ?string
    {
        if (! $this->mailsConfirmations->isEmpty())
        {
            /** @var \Application\Entity\Db\MailConfirmation $mailConfirmation */
            foreach ($this->mailsConfirmations as $mailConfirmation) {
                if ($mailConfirmation->getEtat() === MailConfirmation::CONFIRME) {
                    return $mailConfirmation->getEmail();
                }
            }
        }
        return null;
    }

    /**
     * Retourne l'adresse mail de l'éventuel (premier) utilisateur correspondant à cet individu.
     *
     * **Conseil : voir aussi {@see getEmailBestOf()}.**
     *
     * @return string|null
     */
    public function getEmailUtilisateur(): ?string
    {
        foreach ($this->getUtilisateurs() as $utilisateur) {
            if ($email = $utilisateur->getEmail()) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Set nomPatronymique
     *
     * @param string $nomPatronymique
     *
     * @return self
     */
    public function setNomPatronymique($nomPatronymique)
    {
        $this->nomPatronymique = $nomPatronymique;

        return $this;
    }

    /**
     * Get nomPatronymique
     *
     * @return string
     */
    public function getNomPatronymique()
    {
        return $this->nomPatronymique;
    }

    /**
     * Set nomUsuel
     *
     * @param string $nomUsuel
     *
     * @return self
     */
    public function setNomUsuel($nomUsuel)
    {
        $this->nomUsuel = $nomUsuel;

        return $this;
    }

    /**
     * Get nomUsuel
     *
     * @return string
     */
    public function getNomUsuel()
    {
        return $this->nomUsuel;
    }

    /**
     * @return string
     */
    public function getPrenom1()
    {
        return $this->prenom1;
    }

    /**
     * @param string $prenom1
     * @return Individu
     */
    public function setPrenom1($prenom1)
    {
        $this->prenom1 = $prenom1;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom2()
    {
        return $this->prenom2;
    }

    /**
     * @param string $prenom2
     * @return Individu
     */
    public function setPrenom2($prenom2)
    {
        $this->prenom2 = $prenom2;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom3()
    {
        return $this->prenom3;
    }

    /**
     * @param string $prenom3
     * @return Individu
     */
    public function setPrenom3($prenom3)
    {
        $this->prenom3 = $prenom3;

        return $this;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return self
     */
    public function setPrenom($prenom)
    {
        return $this->setPrenom1($prenom);
    }

    /**
     * Get prenom
     *
     * @param bool $tous
     * @return string
     */
    public function getPrenom($tous = false)
    {
        return $tous ? $this->getPrenoms() : $this->getPrenom1();
    }

    /**
     * Get prenoms
     *
     * @return string
     */
    public function getPrenoms()
    {
        return join(' ', array_filter([
            $this->getPrenom1(),
            $this->getPrenom2(),
            $this->getPrenom3(),
        ]));
    }

    /**
     * Set civilite
     *
     * @param string|null $civilite
     *
     * @return self
     */
    public function setCivilite(?string $civilite)
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * Get civilite
     *
     * @return string|null
     */
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * Get civilite
     *
     * @return string
     */
    public function getCiviliteToString()
    {
        return $this->getCivilite();
    }

    /**
     * @return string 'F' ou 'M'
     */
    public function getCiviliteAsLetter(): string
    {
        return [
            self::CIVILITE_M => 'M',
            self::CIVILITE_MME => 'F',
        ][$this->getCivilite()];
    }

    /**
     * Get estUneFemme
     *
     * @return bool
     */
    public function estUneFemme()
    {
        return 'Mme' === $this->getCivilite();
    }



    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        $f = new NomCompletFormatter(true, true);

        return $f->filter($this);
    }

    /**
     * Get nomUsuel
     *
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenoms
     * @param bool $prenomfirst
     * @param bool $court
     * @param bool $patroPlutotQueUsuel
     * @return string
     */
    public function getNomComplet(
        $avecCivilite = false,
        $avecNomPatro = false,
        $prenoms = false,
        $prenomfirst = false,
        $court = false,
        $patroPlutotQueUsuel=false)
    {
        $f = new NomCompletFormatter(true, $avecCivilite, $avecNomPatro, $prenomfirst, $prenoms, $court, $patroPlutotQueUsuel);

        return $f->filter($this);
    }

    /**
     * Get dateNaissance
     *
     * @return string
     */
    public function getDateNaissanceToString()
    {
        if ($this->dateNaissance === null) return "";
        return $this->dateNaissance->format(Constants::DATE_FORMAT);
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
     *
     * @return Individu
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode
     *
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * @param Role $role
     * @return self
     */
    public function addRole(Role $role)
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * @param Role $role
     * @return self
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @return \Application\Entity\Db\Utilisateur[]
     */
    public function getUtilisateurs()
    {
        return $this->utilisateurs->toArray();
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Individu
     */
    public function setType(string $type): Individu
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Etablissement|null
     */
    public function getEtablissement(): ?Etablissement
    {
        $complement = $this->getComplement();
        if ($complement AND !$complement->estHistorise() AND $complement->getEtablissement() !== null) return $complement->getEtablissement();
        return $this->etablissement;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return Individu
     */
    public function setEtablissement(?Etablissement $etablissement): Individu
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return \Application\Entity\Db\Pays|null
     */
    public function getPaysNationalite(): ?Pays
    {
        return $this->paysNationalite;
    }

    /**
     * @param \Application\Entity\Db\Pays|null $pays
     * @return self
     */
    public function setPaysNationalite(?Pays $pays): self
    {
        $this->paysNationalite = $pays;
        return $this;
    }

    /**
     * @return Etablissement|null
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        $complement = $this->getComplement();
        if ($complement AND !$complement->estHistorise() AND $complement->getUniteRecherche() !== null) return $complement->getUniteRecherche();
        return null;
    }

    /**
     * @return IndividuCompl|null
     */
    public function getComplement() : ?IndividuCompl
    {
        return $this->complements->first() ?: null;
    }


    /**
     * @inheritDoc
     */
    public function getResourceId(): string
    {
        return 'Individu';
    }
}