<?php

namespace Individu\Entity\Db;

use Application\Constants;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\Pays;
use Application\Entity\Db\Role;
use Application\Filter\NomCompletFormatter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Individu
 */
class Individu implements
    HistoriqueAwareInterface, SourceAwareInterface, ResourceInterface,
    SubstitutionAwareEntityInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use SubstitutionAwareEntityTrait;

    const CIVILITE_M = 'M.';
    const CIVILITE_MME = 'Mme';

    const TYPE_ACTEUR = 'acteur';
    const TYPE_DOCTORANT = 'doctorant';

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

    protected bool $apatride = false;

    /**
     * @var \DateTime
     */
    protected $dateNaissance;

    /**
     * @var string
     */
    protected $emailPro;

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
     * @var string|null
     */
    private ?string $idRef = null;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var ArrayCollection
     */
    private $roles;

    /**
     * @var ArrayCollection|MailConfirmation[]
     */
    private $mailsConfirmations;

    /**
     * @var ArrayCollection
     */
    private $utilisateurs;

    /**
     * @var \Application\Entity\Db\Pays|null
     */
    private $paysNationalite = null;

    /**
     * @var ArrayCollection|IndividuCompl[]
     */
    private $complements;

    /**
     * Fabrique un tableau permettant d'alimenter un select.
     *
     * @param \Individu\Entity\Db\Individu[] $individus
     * @return string[] id individu => nom complet sans civilité
     */
    static public function asSelectValuesOptions(array $individus): array
    {
        $valuesOptions = [];
        foreach ($individus as $i) {
            $valuesOptions[$i->getId()] = $i->getNomComplet();
        }

        return $valuesOptions;
    }

    /**
     * Individu constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->mailsConfirmations = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
        $this->complements = new ArrayCollection();
        $this->substitues = new ArrayCollection();
        $this->substituants = new ArrayCollection();
    }

    /**
     * Get histoModification
     */
    public function getHistoModification(): ?DateTime
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

    public function isApatride(): bool
    {
        return $this->apatride;
    }

    public function setApatride(bool $apatride): self
    {
        $this->apatride = $apatride;
        return $this;
    }

    /**
     * Set emailPro
     *
     * @param string $emailPro
     * @return self
     */
    public function setEmailPro(string $emailPro): self
    {
        $this->emailPro = $emailPro;

        return $this;
    }

    /**
     * Retourne l'adresse électronique professionnelle/institutionnelle de cet individu.
     *
     * @param bool $useComplement Si `true` et qu'une adresse non null existe dans le "complément d'individu" éventuel,
     * c'est elle qui est retournée.
     * @return string|null
     */
    public function getEmailPro(bool $useComplement = true): ?string
    {
        $email = $useComplement && $this->getEmailProComplement() ?
            $this->getEmailProComplement() :
            $this->emailPro;

        if ($email === null || trim($email) === '') {
            return null;
        }

        return $email;
    }

    /**
     * Retourne l'adresse électronique professionnelle/institutionnelle de cet individu renseignée
     * dans le "complément d'individu" éventuel.
     *
     * @return string|null
     */
    public function getEmailProComplement(): ?string
    {
        return $this->getComplement()?->getEmailPro();
    }

    /**
     * Retourne l'adresse mail de contact (renseignée par le doctorant lui-même).
     *
     * À propos des doctorants :
     *   - c'est eux qui renseignent leur adresse de contact ;
     *   - ils consentent ou non à son utilisation pour les listes de diff : {@see getEmailContactAutorisePourListeDiff()}.
     *
     * @return string|null
     */
    public function getEmailContact(): ?string
    {
        if ($mailConfirmation = $this->getMailConfirmationConfirme()) {
            return $mailConfirmation->getEmail();
        }

        return null;
    }

    /**
     * Indique si l'individu a autorisé l'application à utiliser l'adresse mail de contact pour les listes de diffusion.
     *
     * @return bool
     */
    public function getEmailContactAutorisePourListeDiff(): bool
    {
        // ATTENTION : pour l'instant, tant que l'individu n'a pas fait la démarche de refuser, on considère
        // qu'on a l'autorisation ! :-/

        if ($mailConfirmation = $this->getMailConfirmationConfirme()) {
            return !$mailConfirmation->getRefusListeDiff();
        }

        return true;
    }

    /**
     * Retourne l'adresse mail de l'éventuel (premier) utilisateur correspondant à cet individu.
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
     */
    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * Retourne "Mme" ou "M.", ou `null`.
     */
    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    /**
     * Retourne "Madame" ou "Monsieur", ou `null`.
     */
    public function getCiviliteToString(): ?string
    {
        if (!$this->civilite) {
            return null;
        }

        return $this->estUneFemme() ? "Madame" : "Monsieur";
    }

    /**
     * Retourn `true` si cet individu a une civilité correspondant à une femme.
     */
    public function estUneFemme(): bool
    {
        return self::CIVILITE_MME === $this->civilite;
    }

    /**
     * Retourne le nom complet *au format par défaut (recommandé)*.
     *
     * @see getNomComplet()
     */
    public function __toString(): string
    {
        return $this->getNomComplet();
    }

    /**
     * Retourne le nom complet *au format par défaut (recommandé)*.
     *
     * @see getNomCompletFormatter() pour avoir accès aux autres formats.
     */
    public function getNomComplet(): string
    {
        return $this->getNomCompletFormatter()->f();
    }

    /**
     * Retourne une instance du formatteur de nom complet pour cet individu.
     */
    public function getNomCompletFormatter(): NomCompletFormatter
    {
        $formatter = new NomCompletFormatter();
        $formatter->setValue($this);

        return $formatter;
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
     * @return string|null
     */
    public function getIdRef(): ?string
    {
        return $this->idRef;
    }

    /**
     * @param string|null $idRef
     * @return self
     */
    public function setIdRef(?string $idRef): self
    {
        $this->idRef = $idRef;
        return $this;
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
     * Retourne l'eventuel complément d'individu, en écartant ou non les historisés.
     */
    public function getComplement(bool $exclureHistorise = true) : ?IndividuCompl
    {
        $complements = $this->complements;

        if ($exclureHistorise) {
            $complements = $complements->filter(fn(IndividuCompl $ic) => !$ic->estHistorise());
        }

        return $complements->first() ?: null;
    }

    private function getMailConfirmationConfirme(): ?MailConfirmation
    {
        foreach ($this->mailsConfirmations as $mailConfirmation) {
            if ($mailConfirmation->getEtat() === MailConfirmation::CONFIRME) {
                return $mailConfirmation;
            }
        }

        return null;
    }


    public function getTypeSubstitution(): string
    {
        return 'individu';
    }


    /**
     * @inheritDoc
     */
    public function getResourceId(): string
    {
        return 'Individu';
    }

}