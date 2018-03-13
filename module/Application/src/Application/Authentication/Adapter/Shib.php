<?php

namespace Application\Authentication\Adapter;

use phpCAS;
use UnicaenApp\Exception;
use UnicaenAuth\Options\ModuleOptions;
use Zend\Authentication\Exception\ExceptionInterface;
use Zend\Authentication\Exception\UnexpectedValueException;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Http\Response;
use ZfcUser\Authentication\Adapter\AbstractAdapter;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use ZfcUser\Authentication\Adapter\ChainableAdapter;

/**
 * CAS authentication adpater
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Shib extends AbstractAdapter implements ServiceManagerAwareInterface, EventManagerAwareInterface
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
     * @var phpCAS
     */
    protected $casClient;

    /**
     * Réalise l'authentification.
     *
     * @param AuthEvent $e
     * @return Response|null
     * @see ChainableAdapter
     */
    public function authenticate(AuthEvent $e)
    {
	    if ($this->isSatisfied()) {
            try {
                $storage = $this->getStorage()->read();
            } catch (ExceptionInterface $e) {
                throw new Exception\RuntimeException("Erreur de lecture du storage");
            }
            $e
                ->setIdentity($storage['identity'])
                ->setCode(AuthenticationResult::SUCCESS)
                ->setMessages(['Authentication successful.']);
            return null;
        }

        if (empty($_SERVER['REMOTE_USER'])) {

            /** @var Request $request */
            $request = $e->getRequest();
            $returnUrl = $request->getQuery('redirect', false);

            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_302);
            $response->getHeaders()->addHeaders([
                'Location' => "/secure?redirect=" . urlencode($returnUrl),
            ]);

            return $response;
        }

        $eppn = $_SERVER['REMOTE_USER'];

        $e->setIdentity($eppn);
        $this->setSatisfied(true);
        try {
            $storage = $this->getStorage()->read();
            $storage['identity'] = $e->getIdentity();
            $this->getStorage()->write($storage);
        } catch (ExceptionInterface $e) {
            throw new Exception\RuntimeException("Erreur de concernant le storage");
        }
        $e
            ->setCode(AuthenticationResult::SUCCESS)
            ->setMessages(['Authentication successful.']);

        $userData = new ShibUser();
        $userData->setId($eppn);
        $userData->setUsername($eppn);
        $userData->setDisplayName($eppn);
        $userData->setEmail($eppn);

        /* @var $userService \Application\Service\User */
        $userService = $this->getServiceManager()->get('unicaen-auth_user_service');
        $userService->userAuthenticated($e->getIdentity(), $userData);
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