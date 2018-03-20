<?php

namespace Application\Entity;

use UnicaenApp\Entity\Ldap\People;
use UnicaenApp\Exception\LogicException;
use UnicaenAuth\Entity\Db\AbstractUser;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use ZfcUser\Entity\UserInterface;

/**
 * Wrapper représentant un utilisateur authentifié.
 *
 * @author Unicaen
 */
class AuthUserWrapper implements UserInterface
{
    /**
     * @var People|AbstractUser|ShibUser
     */
    private $user;

    /**
     * Instancie à partir d'une entité utilisateur.
     *
     * @param $user People|AbstractUser|ShibUser
     * @return self
     */
    static public function inst($user)
    {
        if (
            !$user instanceof People &&
            !$user instanceof AbstractUser &&
            !$user instanceof ShibUser
        ) {
            throw new LogicException("Type d'utilisateur spécifié invalide");
        }

        return new static($user);
    }

    /**
     * Instancie à partir des données d'identité, si possible.
     *
     * @param array $identity ['ldap' => People|null, 'db' => Utilisateur|null, 'shib' => ShibUser|null]
     * @return AuthUserWrapper|null
     */
    static public function instFromIdentity(array $identity)
    {
        if (isset($identity['ldap'])) {
            /** @var People $userData */
            $userData = $identity['ldap'];
        } elseif (isset($identity['shib'])) {
            /** @var ShibUser $userData */
            $userData = $identity['shib'];
        } else {
//            throw new LogicException("Aucune donnée d'identité LDAP ni Shibboleth disponible");
            return null;
        }

        return new static($userData);
    }

    /**
     * PRIVATE constructor.
     *
     * @param $user People|AbstractUser|ShibUser
     */
    private function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Retourne la partie domaine DNS de l'EPPN, si applicable aux données utilisateur courantes.
     *
     * Retourne par exemple "unicaen.fr" lorsque l'EPPN est "tartempion@unicaen.fr"
     *
     * @return string
     */
    public function getDomainFromEppn()
    {
        $parts = explode('@', $this->getEppn());

        return $parts[1];
    }

    /**
     * Retourne l'EduPersonPrincipalName (EPPN), si applicable aux données utilisateur courantes.
     *
     * @return string
     */
    public function getEppn()
    {
        switch (true) {
            case $this->user instanceof People:
                return $this->user->getEduPersonPrincipalName();
                break;
            case $this->user instanceof AbstractUser:
                throw new LogicException("Non applicable!");
                break;
            case $this->user instanceof ShibUser:
                return $this->user->getEppn();
                break;
        }
    }

    /**
     * @return string
     */
    public function getSupannEmpId()
    {
        switch (true) {
            case $this->user instanceof People:
                return $this->user->getSupannEmpId();
                break;
            case $this->user instanceof AbstractUser:
                throw new LogicException("Non applicable!");
                break;
            case $this->user instanceof ShibUser:
                return $this->user->getId();
                break;
        }
    }

    /**
     * Get id.
     *
     * @return int|string
     */
    public function getId()
    {
        switch (true) {
            case $this->user instanceof People:
                return $this->user->getId();
                break;
            case $this->user instanceof AbstractUser:
            case $this->user instanceof ShibUser:
                return $this->user->getId();
                break;
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
            case $this->user instanceof People:
                return $this->user->getSupannAliasLogin();
                break;
            case $this->user instanceof AbstractUser:
            case $this->user instanceof ShibUser:
                return $this->user->getUsername();
                break;
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
            case $this->user instanceof People:
                return $this->user->getMail();
                break;
            case $this->user instanceof AbstractUser:
            case $this->user instanceof ShibUser:
                return $this->user->getEmail();
                break;
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
            case $this->user instanceof People:
                return $this->user->getNomComplet(true);
                break;
            case $this->user instanceof AbstractUser:
            case $this->user instanceof ShibUser:
                return $this->user->getDisplayName();
                break;
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
            case $this->user instanceof People:
                return 'ldap';
                break;
            case $this->user instanceof AbstractUser:
                return $this->user->getPassword();
                break;
            case $this->user instanceof ShibUser:
                return 'shib';
                break;
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
            case $this->user instanceof People:
                $parts = ldap_explode_dn($this->user->getDn(), 1);
                $isDeactivated = in_array('deactivated', $parts);
                return $isDeactivated ? 0 : 1;
            case $this->user instanceof AbstractUser:
                return $this->user->getState();
                break;
            case $this->user instanceof ShibUser:
                return 1;
                break;
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