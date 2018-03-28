<?php

namespace Application\Entity;

use UnicaenApp\Entity\Ldap\People as UnicaenAppPeople;
use UnicaenAuth\Authentication\Storage\ChainEvent as StorageChainEvent;
use UnicaenLdap\Entity\People as UnicaenLdapPeople;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\AbstractUser;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use Zend\Authentication\Exception\ExceptionInterface;
use ZfcUser\Entity\UserInterface;

/**
 * Wrapper représentant un utilisateur authentifié.
 *
 * @author Unicaen
 */
class UserWrapper implements UserInterface
{
    /**
     * @var UnicaenLdapPeople|UnicaenAppPeople|AbstractUser|ShibUser
     */
    private $user;

    /**
     * Factory method.
     *
     * Instancie à partir d'une entité utilisateur.
     *
     * @param $user UnicaenLdapPeople|UnicaenAppPeople|AbstractUser|ShibUser
     * @return self
     */
    static public function inst($user)
    {
        if (
            !$user instanceof UnicaenLdapPeople &&
            !$user instanceof UnicaenAppPeople &&
            !$user instanceof AbstractUser &&
            !$user instanceof ShibUser
        ) {
            throw new LogicException("Type d'utilisateur spécifié invalide");
        }

        return new static($user);
    }

    /**
     * Factory method.
     *
     * Instancie à partir des données issues d'un StorageChainEvent, si possible.
     *
     * @param StorageChainEvent $event
     * @return UserWrapper|null
     */
    static public function instFromStorageChainEvent(StorageChainEvent $event)
    {
        try {
            $contents = $event->getContents();
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Impossible de lire le storage");
        }

        if (null === $contents['ldap'] && null === $contents['shib']) {
            return null;
        }

        return new static($contents['ldap'] ?: $contents['shib']);
    }

    /**
     * Factory method.
     *
     * Instancie à partir des données d'identité, si possible.
     *
     * @param array $identity ['ldap' => People|null, 'db' => Utilisateur|null, 'shib' => ShibUser|null]
     * @return UserWrapper|null
     */
    static public function instFromIdentity(array $identity)
    {
        if (isset($identity['ldap'])) {
            /** @var UnicaenAppPeople $userData */
            $userData = $identity['ldap'];
        } elseif (isset($identity['shib'])) {
            /** @var ShibUser $userData */
            $userData = $identity['shib'];
        } else {
            return null;
        }

        return new static($userData);
    }

    /**
     * Factory method.
     *
     * Instancie à partir d'un événement UserAuthenticatedEvent.
     *
     * @param UserAuthenticatedEvent $event
     * @return UserWrapper
     */
    static public function instFromUserAuthenticatedEvent(UserAuthenticatedEvent $event)
    {
        if ($event->getLdapUser()) {
            $user = $event->getLdapUser();
        } elseif ($event->getShibUser()) {
            $user = $event->getShibUser();
        } elseif ($event->getDbUser()) {
            $user = $event->getDbUser();
        } else {
            throw new LogicException("L'événement ne fournit aucune entité utilisateur!");
        }

        return new static($user);
    }

    /**
     * PRIVATE constructor.
     *
     * @param $user UnicaenLdapPeople|UnicaenAppPeople|AbstractUser|ShibUser
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
    protected function getSupannEmpId()
    {
        switch (true) {
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
     * @return string
     */
    protected function getSupannEtuId()
    {
        switch (true) {
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
                return $this->user->getSupannEtuId();
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
     * Retourne soit le supannEmpId soit le supannEtuId, car l'un ou l'autre est forcément null.
     *
     * @return string
     */
    public function getSupannId()
    {
        return $this->getSupannEmpId() ?: $this->getSupannEtuId();
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        switch (true) {
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
                return $this->user->getSn(true);
                break;
            case $this->user instanceof AbstractUser:
                throw new RuntimeException("Cas non implementé car la classe AbstractUser n'a pas de propriété 'nom'");
                break;
            case $this->user instanceof ShibUser:
                return $this->user->getNom();
                break;
        }
    }

    /**
     * Get prenom.
     *
     * @return string
     */
    public function getPrenom()
    {
        switch (true) {
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
                return $this->user->getGivenName();
                break;
            case $this->user instanceof AbstractUser:
                throw new RuntimeException("Cas non implementé car la classe AbstractUser n'a pas de propriété 'prenom'");
                break;
            case $this->user instanceof ShibUser:
                return $this->user->getPrenom();
                break;
        }
    }

    /**
     * Get civilite.
     *
     * @return string
     */
    public function getCivilite()
    {
        switch (true) {
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
                return $this->user->getSupannCivilite();
                break;
            case $this->user instanceof AbstractUser:
                throw new RuntimeException("Cas non implementé car la classe AbstractUser n'a pas de propriété 'civilite'");
                break;
            case $this->user instanceof ShibUser:
                return $this->user->getCivilite();
                break;
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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
            case $this->user instanceof UnicaenLdapPeople:
            case $this->user instanceof UnicaenAppPeople:
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