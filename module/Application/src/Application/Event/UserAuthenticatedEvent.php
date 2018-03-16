<?php

namespace Application\Event;

use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenApp\Entity\Ldap\People;
use ZfcUser\Entity\UserInterface;

/**
 * Classe des événements déclenchés lors de l'authentification de l'utilisateur.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserAuthenticatedEvent extends \UnicaenAuth\Event\UserAuthenticatedEvent
{
    const PRE_PERSIST      = 'prePersist';
    const PARAM_DB_USER    = 'db_user';
    const PARAM_LDAP_USER  = 'ldap_user';
    const PARAM_SHIB_USER  = 'shib_user';
    
    /**
     * Retourne l'entité utilisateur issue de la base de données.
     * 
     * @return UserInterface
     */
    public function getDbUser()
    {
        return $this->getParam(self::PARAM_DB_USER);
    }

    /**
     * Retourne l'entité utilisateur issue de l'annuaire LDAP.
     * 
     * @return People
     */
    public function getLdapUser()
    {
        return $this->getParam(self::PARAM_LDAP_USER);
    }

    /**
     * Spécifie l'entité utilisateur issue de la base de données.
     * 
     * @param UserInterface $dbUser
     * @return UserAuthenticatedEvent
     */
    public function setDbUser(UserInterface $dbUser)
    {
        $this->setParam(self::PARAM_DB_USER, $dbUser);
        return $this;
    }

    /**
     * Spécifie l'entité utilisateur issue de l'annuaire LDAP.
     * 
     * @param People $ldapUser
     * @return UserAuthenticatedEvent
     */
    public function setLdapUser(People $ldapUser)
    {
        $this->setParam(self::PARAM_LDAP_USER, $ldapUser);
        return $this;
    }

    /**
     * Retourne l'entité utilisateur issue de l'authentification Shibboleth.
     *
     * @return ShibUser
     */
    public function getShibUser()
    {
        return $this->getParam(self::PARAM_SHIB_USER);
    }

    /**
     * Spécifie l'entité utilisateur issue de l'authentification Shibboleth.
     *
     * @param ShibUser $shibUser
     * @return UserAuthenticatedEvent
     */
    public function setShibUser(ShibUser $shibUser)
    {
        $this->setParam(self::PARAM_SHIB_USER, $shibUser);
        return $this;
    }
}