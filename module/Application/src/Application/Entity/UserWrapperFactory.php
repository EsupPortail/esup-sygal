<?php

namespace Application\Entity;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Utilisateur;
use Application\Exception\DomainException;
use UnicaenAuth\Entity\Ldap\People as UnicaenAuthPeople;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Authentication\Storage\ChainEvent as StorageChainEvent;
use UnicaenAuth\Entity\Shibboleth\ShibUser;
use UnicaenAuth\Event\UserAuthenticatedEvent;
use Laminas\Authentication\Exception\ExceptionInterface;
use UnicaenAuth\Options\ModuleOptions;

/**
 * @author Unicaen
 */
class UserWrapperFactory
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @param \UnicaenAuth\Options\ModuleOptions $authModuleOptions
     */
    public function setModuleOptions(ModuleOptions $authModuleOptions)
    {
        $this->options = $authModuleOptions;
    }

    /**
     * Factory method.
     *
     * Instancie à partir des données issues d'un StorageChainEvent, si possible.
     *
     * @param StorageChainEvent $event
     * @return UserWrapper|null
     * @throws \Exception
     */
    public function createInstanceFromStorageChainEvent(StorageChainEvent $event)
    {
        $inst = new UserWrapper();

        try {
            $contents = $event->getContents();
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Impossible de lire le storage");
        }

        $userData = $this->extractUserDataFromArray($contents);
        if ($userData === null) {
            return null;
        }

        $individu = $this->extractIndividuFromArray($contents);

        $inst->setUserData($userData);
        $inst->setIndividu($individu);

        return $inst;
    }

    /**
     * Factory method.
     *
     * Instancie à partir des données d'identité, si possible.
     *
     * @param array $identity ['ldap' => People|null, 'db' => Utilisateur|null, 'shib' => ShibUser|null]
     * @return UserWrapper
     * @throws \Exception
     */
    public function createInstanceFromIdentity(array $identity)
    {
        $inst = new UserWrapper();

        $userData = $this->extractUserDataFromArray($identity);
        $individu = $this->extractIndividuFromArray($identity);

        $inst->setUserData($userData);
        $inst->setIndividu($individu);

        return $inst;
    }

    /**
     * Factory method.
     *
     * Instancie à partir d'un événement UserAuthenticatedEvent.
     *
     * @param UserAuthenticatedEvent $event
     * @return UserWrapper
     * @throws \Exception
     */
    public function createInstanceFromUserAuthenticatedEvent(UserAuthenticatedEvent $event): UserWrapper
    {
        $inst = new UserWrapper();

        if ($event->getLdapUser()) {
            $userData = $event->getLdapUser();
        } elseif ($event->getShibUser()) {
            $userData = $event->getShibUser();
        } elseif ($event->getDbUser()) {
            $userData = $event->getDbUser();
        } else {
            throw new DomainException("L'événement ne fournit aucune entité utilisateur!");
        }

        $individu = null;
        if ($event->getDbUser()) {
            /** @var Utilisateur $utilisateur */
            $utilisateur = $event->getDbUser();
            $individu = $utilisateur->getIndividu();
        }

        $inst->setUserData($userData);
        $inst->setIndividu($individu);

        return $inst;
    }

    /**
     * @param array $contents
     * @return Individu|null
     */
    private function extractIndividuFromArray(array $contents)
    {
        $individu = null;

        if (isset($contents['db'])) {
            /** @var Utilisateur $utilisateur */
            $utilisateur = $contents['db'];
            $individu = $utilisateur->getIndividu();
        }

        return $individu;
    }

    /**
     * @param array $contents
     * @return UnicaenAuthPeople|Utilisateur|ShibUser|null
     */
    private function extractUserDataFromArray(array $contents)
    {
        // NB: 'db' en dernier

        if (isset($contents['ldap'])) {
            /** @var UnicaenAuthPeople $userData */
            $userData = $contents['ldap'];
        } elseif (isset($contents['shib'])) {
            /** @var ShibUser $userData */
            $userData = $contents['shib'];
        } elseif (isset($contents['db'])) {
            /** @var Utilisateur $userData */
            $userData = $contents['db'];
        } else {
            $userData = null;
        }

        return $userData;
    }
}