<?php

namespace Application\Authentication\Adapter;

use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Options\ModuleOptions;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Shibboleth authentication adpater
 *
 * @author Unicaen
 */
class Shib implements ServiceManagerAwareInterface, EventManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var array
     */
    protected $shibOptions;

    /**
     * @var ShibUser
     */
    protected $authenticatedUser;

    /**
     * @return ShibUser|null
     */
    public function getAuthenticatedUser()
    {
        if ($this->authenticatedUser === null) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                return null;
            }
            $this->authenticatedUser = $this->createShibUser();
        }

        return $this->authenticatedUser;
    }

    /**
     * @return ShibUser
     */
    private function createShibUser()
    {
        $eppn = $_SERVER['REMOTE_USER'];

        if (isset($_SERVER['supannEtuId'])) {
            $id = $_SERVER['supannEtuId'];
        } elseif (isset($_SERVER['supannEmpId'])) {
            $id = $_SERVER['supannEmpId'];
        } else {
            throw new RuntimeException('Un au moins des attributs suivants doivent exister dans $_SERVER : supannEtuId, supannEmpId.');
        }

        $mail = null;
        if (isset($_SERVER['mail'])) {
            $mail = $_SERVER['mail'];
        }

        $displayName = null;
        if (isset($_SERVER['displayName'])) {
            $displayName = $_SERVER['displayName'];
        }

        $shibUser = new ShibUser();
        $shibUser->setId($id);
        $shibUser->setUsername($eppn);
        $shibUser->setDisplayName($displayName);
        $shibUser->setEmail($mail);

        return $shibUser;
    }

    /**
     * @param ModuleOptions $options
     */
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $options = array_merge(
                    $this->getServiceManager()->get('zfcuser_module_options')->toArray(),
                    $this->getServiceManager()->get('unicaen-auth_module_options')->toArray());
            $this->setOptions(new ModuleOptions($options));
        }
        return $this->options;
    }

    /**
     * Get service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return self
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Retrieve EventManager instance
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return self
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }
}