<?php

namespace Application\Authentication\Storage;

use Application\Authentication\Adapter\ShibUser;
use UnicaenAuth\Authentication\Storage\ChainableStorage;
use UnicaenAuth\Authentication\Storage\ChainEvent;
use UnicaenAuth\Entity\Ldap\People;
use UnicaenAuth\Options\ModuleOptions;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\Storage\StorageInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;
use Application\Authentication\Adapter\Shib as ShibAdapter;

/**
 * Shibboleth authentication storage.
 *
 * @author Unicaen
 */
class Shib implements ChainableStorage, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var StorageInterface
     */
    protected $storage;
    
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var People
     */
    protected $resolvedIdentity;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @param ChainEvent $e
     * @return ShibUser
     * @throws \Zend\Authentication\Exception\ExceptionInterface
     */
    public function read(ChainEvent $e)
    {
        /** @var ShibAdapter $shib */
        $shib = $this->getServiceLocator()->get(ShibAdapter::class);
        $shibUser = $shib->getAuthenticatedUser();

        $e->addContents('shib', $shibUser);
        
        return $shibUser;
    }

    /**
     * Writes $contents to storage
     *
     * @param ChainEvent $e
     * @throws \Zend\Authentication\Exception\ExceptionInterface
     */
    public function write(ChainEvent $e)
    {
        $contents = $e->getParam('contents');
        $this->resolvedIdentity = null;
        $this->getStorage()->write($contents);
    }

    /**
     * Clears contents from storage
     *
     * @param ChainEvent $e
     * @throws \Zend\Authentication\Exception\ExceptionInterface
     */
    public function clear(ChainEvent $e)
    {
        $this->resolvedIdentity = null;
        $this->getStorage()->clear();
    }

    /**
     * getStorage
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->setStorage(new Session());
        }

        return $this->storage;
    }

    /**
     * setStorage
     *
     * @param StorageInterface $storage
     * @return self
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }
}
