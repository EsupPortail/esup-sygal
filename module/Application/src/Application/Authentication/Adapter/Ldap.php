<?php

namespace Application\Authentication\Adapter;

use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Options\ModuleOptions;
use Zend\Authentication\Exception\UnexpectedValueException;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\Adapter\Ldap as LdapAuthAdapter;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcUser\Authentication\Adapter\AbstractAdapter;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use ZfcUser\Authentication\Adapter\ChainableAdapter;
use UnicaenApp\Mapper\Ldap\People as LdapPeopleMapper;
use Zend\Authentication\Exception\ExceptionInterface;

/**
 * LDAP authentication adpater
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Ldap extends AbstractAdapter implements ServiceManagerAwareInterface, EventManagerAwareInterface
{
    const USURPATION_USERNAMES_SEP = '=';

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var LdapAuthAdapter
     */
    protected $ldapAuthAdapter;

    /**
     * @var LdapPeopleMapper
     */
    protected $ldapPeopleMapper;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var string
     */
    protected $usernameUsurpe;

    /**
     *
     * @param AuthEvent $e
     * @return boolean
     * @throws UnexpectedValueException
     * @see ChainableAdapter
     */
    public function authenticate(AuthEvent $e)
    {
        if ($this->isSatisfied()) {
            try {
                $storage = $this->getStorage()->read();
            } catch (ExceptionInterface $e) {
                throw new RuntimeException("Erreur de lecture du storage");
            }
            $e->setIdentity($storage['identity'])
                    ->setCode(AuthenticationResult::SUCCESS)
                    ->setMessages(['Authentication successful.']);
            return;
        }

        $username   = $e->getRequest()->getPost()->get('identity');
        $credential = $e->getRequest()->getPost()->get('credential');

        $success = $this->authenticateUsername($username, $credential);

        // Failure!
        if (! $success) {
            $e->setCode(AuthenticationResult::FAILURE)
              ->setMessages(['LDAP bind failed.']);
            $this->setSatisfied(false);
            return false;
        }

        // recherche de l'individu dans l'annuaire LDAP
        $ldapPeople = $this->getLdapPeopleMapper()->findOneByUsername($username);
        if (!$ldapPeople) {
            $e
                ->setCode(AuthenticationResult::FAILURE)
                ->setMessages(['Authentication failed.']);
            $this->setSatisfied(false);
            return false;
        }

        $e->setIdentity($this->usernameUsurpe ?: $username);
        $this->setSatisfied(true);
        try {
            $storage = $this->getStorage()->read();
            $storage['identity'] = $e->getIdentity();
            $this->getStorage()->write($storage);
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur de concernant le storage");
        }
        $e->setCode(AuthenticationResult::SUCCESS)
          ->setMessages(['Authentication successful.']);

        /* @var $userService \Application\Service\User */
        $userService = $this->getServiceManager()->get('unicaen-auth_user_service');
        $userService->userAuthenticated($ldapPeople);
    }

    /**
     * Extrait le loginUsurpateur et le loginUsurpé si l'identifiant spécifé est de la forme
     * "loginUsurpateur=loginUsurpé".
     *
     * @param string $identifiant Identifiant, éventuellement de la forme "loginUsurpateur=loginUsurpé"
     * @return array
     * [loginUsurpateur, loginUsurpé] si l'identifiant est de la forme "loginUsurpateur=loginUsurpé" ;
     * [] sinon.
     */
    static public function extractUsernamesUsurpation($identifiant)
    {
        if (strpos($identifiant, self::USURPATION_USERNAMES_SEP) > 0) {
            list($identifiant, $usernameUsurpe) = explode(self::USURPATION_USERNAMES_SEP, $identifiant, 2);

            return [
                $identifiant,
                $usernameUsurpe
            ];
        }

        return [];
    }

    /**
     * Authentifie l'identifiant et le mot de passe spécifiés.
     *
     * @param string $username Identifiant de connexion
     * @param string $credential Mot de passe
     * @return boolean
     */
    public function authenticateUsername($username, $credential)
    {
        // si 2 logins sont fournis, cela active l'usurpation d'identité (à n'utiliser que pour les tests) :
        // - le format attendu est "loginUsurpateur=loginUsurpé"
        // - le mot de passe attendu est celui du compte usurpateur (loginUsurpateur)
        $this->usernameUsurpe = null;
        $usernames = self::extractUsernamesUsurpation($username);
        if (count($usernames) === 2) {
            list ($username, $this->usernameUsurpe) = $usernames;
            if (!in_array($username, $this->getOptions()->getUsurpationAllowedUsernames())) {
                $this->usernameUsurpe = null;
            }
        }

        // LDAP auth
        $result  = $this->getLdapAuthAdapter()->setUsername($username)->setPassword($credential)->authenticate();
        $success = $result->isValid();

        // verif existence du login usurpé
        if ($this->usernameUsurpe) {
            // s'il nexiste pas, échec de l'authentification
            if (!$this->getLdapAuthAdapter()->getLdap()->searchEntries("(supannAliasLogin=$this->usernameUsurpe)")) {
                $this->usernameUsurpe = null;
                $success              = false;
            }
        }

        return $success;
    }

    /**
     * get ldap people mapper
     *
     * @return LdapPeopleMapper
     */
    public function getLdapPeopleMapper()
    {
        if (null === $this->ldapPeopleMapper) {
            $this->ldapPeopleMapper = $this->getServiceManager()->get('ldap_people_mapper');
        }
        return $this->ldapPeopleMapper;
    }

    /**
     * set ldap people mapper
     *
     * @param LdapPeopleMapper $mapper
     * @return self
     */
    public function setLdapPeopleMapper(LdapPeopleMapper $mapper)
    {
        $this->ldapPeopleMapper = $mapper;
        return $this;
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
     * @return \UnicaenApp\Options\ModuleOptions
     */
    public function getAppModuleOptions()
    {
        return $this->getServiceManager()->get('unicaen-app_module_options');
    }

    /**
     * get ldap connection adapter
     *
     * @return LdapAuthAdapter
     */
    public function getLdapAuthAdapter()
    {
        if (null === $this->ldapAuthAdapter) {
            $options = [];
            if (($config = $this->getAppModuleOptions()->getLdap())) {
                foreach ($config['connection'] as $name => $connection) {
                    $options[$name] = $connection['params'];
                }
            }
            $this->ldapAuthAdapter = new LdapAuthAdapter($options); // NB: array(array)
        }
        return $this->ldapAuthAdapter;
    }

    /**
     * set ldap connection adapter
     *
     * @param LdapAuthAdapter $authAdapter
     * @return Ldap
     */
    public function setLdapAuthAdapter(LdapAuthAdapter $authAdapter)
    {
        $this->ldapAuthAdapter = $authAdapter;
        return $this;
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
     * @return Ldap
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
     * @return Ldap
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers([
            __NAMESPACE__,
            __CLASS__,
        ]);
        $this->eventManager = $eventManager;
        return $this;
    }
}