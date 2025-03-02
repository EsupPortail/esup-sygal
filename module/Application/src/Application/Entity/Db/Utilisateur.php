<?php

namespace Application\Entity\Db;

use BjyAuthorize\Provider\Role\ProviderInterface;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use UnicaenApp\Entity\UserInterface;
use UnicaenUtilisateur\Entity\Db\AbstractUser;
use UnicaenUtilisateur\Entity\Db\RoleInterface;

/**
 * Classe Utilisateur.
 *
 * NB: hérite de AbstractUser uniquement pour pouvoir utiliser HistoriqueListener.
 */
class Utilisateur extends AbstractUser implements UserInterface, ProviderInterface, IndividuAwareInterface
{
    const APP_UTILISATEUR_ID = 1; // indispensable à UnicaenImport !
    const APP_UTILISATEUR_USERNAME = 'sygal-app';

    private ?string $nom = null;
    private ?string $prenom = null;
    protected $state = 1;

    /**
     * @var Individu
     */
    protected $individu;

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return self
     */
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     * @return self
     */
    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    public function setIndividu(?Individu $individu = null): self
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function removeRole(RoleInterface $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }
}