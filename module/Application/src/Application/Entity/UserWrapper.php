<?php

namespace Application\Entity;

use Application\Entity\Db\Utilisateur;
use Individu\Entity\Db\Individu;
use Laminas\Ldap\Exception\LdapException;
use UnicaenApp\Entity\Ldap\People as UnicaenAppPeople;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\AbstractUser;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenLdap\Entity\People as UnicaenLdapPeople;
use ZfcUser\Entity\UserInterface;

/**
 * Wrapper représentant un utilisateur authentifié, permettant de masquer autant que faire se peut
 * les différences entre les classes d'utilisateurs pouvant être rencontrées dans l'appli :
 * - UnicaenLdapPeople
 * - UnicaenAppPeople
 * - Utilisateur
 * - ShibUser
 *
 * @author Unicaen
 */
class UserWrapper implements UserInterface
{
    /**
     * @var UnicaenLdapPeople|UnicaenAppPeople|Utilisateur|ShibUser
     */
    private $userData;

    private string $ldapAttributeNameForUsername;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * @param Utilisateur|UnicaenAppPeople|ShibUser|UnicaenLdapPeople $userData
     */
    public function setUserData($userData): self
    {
        switch (true) {
            case $userData instanceof UnicaenLdapPeople:
            case $userData instanceof UnicaenAppPeople:
            case $userData instanceof ShibUser:
            case $userData instanceof Utilisateur:
                $this->userData = $userData;
                break;
            default:
                throw new LogicException(
                    "Type de données utilisateurs spécifié inattendu : " .
                    (is_object($userData) ? get_class($userData) : gettype($userData))
                );
        }

        if ($this->userData instanceof Utilisateur) {
            $this->individu = $this->userData->getIndividu();
        }

        return $this;
    }

    public function setLdapAttributeNameForUsername(string $ldapAttributeNameForUsername): self
    {
        $this->ldapAttributeNameForUsername = $ldapAttributeNameForUsername;

        return $this;
    }

    /**
     * @param Individu|null $individu
     * @return UserWrapper
     */
    public function setIndividu(Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Retourne l'Individu correpondant à l'utilisateur authentifié, si disponible.
     *
     * @return Individu|null
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * Retourne la partie domaine DNS de l'EPPN, si applicable aux données utilisateur courantes.
     *
     * Retourne par exemple "unicaen.fr" lorsque l'EPPN est "tartempion@unicaen.fr"
     *
     * @return string|null
     */
    public function getDomainFromEppn(): ?string
    {
        if ($eppn = $this->getEppn()) {
            $parts = explode('@', $eppn);

            return $parts[1] ?? null;
        }

        return null;
    }

    /**
     * Retourne la partie domaine DNS de l'adresse email.
     *
     * Retourne par exemple "unicaen.fr" lorsque l'email est "paul.hochon@unicaen.fr"
     *
     * @return string
     */
    public function getDomainFromEmail()
    {
        $parts = explode('@', $this->getEmail());

        return $parts[1];
    }

    /**
     * Retourne l'EduPersonPrincipalName (EPPN), si applicable aux données utilisateur courantes.
     *
     * @return string|null
     */
    public function getEppn(): ?string
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return $this->userData->getEduPersonPrincipalName();

            case $this->userData instanceof ShibUser:
                return $this->userData->getEppn();

            case $this->userData instanceof Utilisateur && $this->userData->getPassword() === AbstractUser::PASSWORD_SHIB:
                return $this->userData->getUsername();

            default:
                return null;
        }
    }

    /**
     * Retourne :
     * - soit le "supann{Ref|Emp|Etu}Id" issu des données utilisateur ;
     * - soit le "supannId" des données individu éventuelles ;
     * - soit null.
     *
     * @return string|null
     */
    public function getSupannId()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                $supannId = $this->userData->getSupannEmpId() ?: $this->userData->getSupannEtuId();
                break;

            case $this->userData instanceof ShibUser:
                $supannId = $this->userData->getId();
                break;

            default:
                $supannId = null;
        }

        if ($supannId !== null) {
            return $supannId;
        }

        if ($this->individu) {
            return $this->individu->getSupannId();
        }

        return null;
    }

    /**
     * Get nom.
     *
     * @return string|null
     */
    public function getNom()
    {
        if ($this->individu !== null) {
            return $this->individu->getNomUsuel();
        }

        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return $this->userData->getSn(true);

            case $this->userData instanceof Utilisateur:
            case $this->userData instanceof ShibUser:
                return $this->userData->getNom();

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Get prenom.
     *
     * @return string|null
     */
    public function getPrenom()
    {
        if ($this->individu !== null) {
            return $this->individu->getPrenom();
        }

        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return $this->userData->getGivenName();

            case $this->userData instanceof Utilisateur:
            case $this->userData instanceof ShibUser:
                return $this->userData->getPrenom();

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Get civilite.
     *
     * @return string
     */
    public function getCivilite()
    {
        if ($this->individu !== null) {
            return $this->individu->getCivilite();
        }

        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return $this->userData->getSupannCivilite();

            case $this->userData instanceof Utilisateur:
                throw new RuntimeException("Cas non implementé car la classe Utilisateur n'a pas de propriété 'civilite'");

            case $this->userData instanceof ShibUser:
                return $this->userData->getCivilite();

            default:
                throw new LogicException("Cas imprévu!");

        }
    }


    ///////////////////////////////// Implémentation de UserInterface ////////////////////////////

    /**
     * Get id.
     *
     * @return int|string
     */
    public function getId()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
            case $this->userData instanceof Utilisateur:
            case $this->userData instanceof ShibUser:
                return $this->userData->getId();

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Set id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        throw new \BadMethodCallException("Interdit!");
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
                try {
                    return $this->userData->get($this->ldapAttributeNameForUsername);
                } catch (LdapException $e) {
                    throw new \RuntimeException(
                        "Impossible d'obtenir la valeur de l'attribut LDAP " . $this->ldapAttributeNameForUsername
                    );
                }

            case $this->userData instanceof UnicaenAppPeople:
            case $this->userData instanceof Utilisateur:
            case $this->userData instanceof ShibUser:
                return $this->userData->getUsername();

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Set username.
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        throw new \BadMethodCallException("Interdit!");
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return $this->userData->getMail();

            case $this->userData instanceof Utilisateur:
            case $this->userData instanceof ShibUser:
                return $this->userData->getEmail();

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Set email.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        throw new \BadMethodCallException("Interdit!");
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return $this->userData->getNomComplet(true);

            case $this->userData instanceof Utilisateur:
            case $this->userData instanceof ShibUser:
                return $this->userData->getDisplayName();

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        throw new \BadMethodCallException("Interdit!");
    }

    /**
     * Get password.
     *
     * @return string password
     */
    public function getPassword()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                return 'ldap';

            case $this->userData instanceof Utilisateur:
                return $this->userData->getPassword();

            case $this->userData instanceof ShibUser:
                return 'shib';

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Set password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        throw new \BadMethodCallException("Interdit!");
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        switch (true) {
            case $this->userData instanceof UnicaenLdapPeople:
            case $this->userData instanceof UnicaenAppPeople:
                $parts = ldap_explode_dn($this->userData->getDn(), 1);
                $isDeactivated = in_array('deactivated', $parts);
                return $isDeactivated ? 0 : 1;

            case $this->userData instanceof Utilisateur:
                return $this->userData->getState();

            case $this->userData instanceof ShibUser:
                return 1;

            default:
                throw new LogicException("Cas imprévu!");
        }
    }

    /**
     * Set state.
     *
     * @param int $state
     */
    public function setState($state)
    {
        throw new \BadMethodCallException("Interdit!");
    }
}