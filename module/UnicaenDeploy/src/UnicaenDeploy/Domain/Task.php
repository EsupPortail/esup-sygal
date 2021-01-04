<?php

namespace UnicaenDeploy\Domain;

use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

abstract class Task implements TaskInterface
{
    /**
     * @var Host
     */
    protected $host;

    /**
     * @var array
     */
    protected $config;

    /**
     * @return Host
     */
    public function getHost(): Host
    {
        return $this->host;
    }

    /**
     * @param Host $host
     * @return Task
     */
    public function setHost(Host $host): Task
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return Task
     */
    public function setConfig(array $config): Task
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param array $args
     * @return string
     */
    protected function argsToString(array $args)
    {
        Assert::allScalar($args, "Argument non scalaire rencontré");

        return implode(' ', array_map(function($key, $value) {
            return $key . '=' . $value;
        }, array_keys($args), $args));
    }

    protected function fillVarsInString(string $text, array $args)
    {
        $text = str_replace('${ROOT_DIR}', $args['ROOT_DIR'] ?? '', $text);
        $text = str_replace('${DESTINATION}', $args['DESTINATION'] ?? '', $text);
        $text = str_replace('${PHP_VERSION}', $args['PHP_VERSION'] ?? '', $text);
        $text = str_replace('${APPDIR}', $args['APPDIR'] ?? '', $text);

        return $text;
    }

    /**
     * @return resource
     */
    protected function sshConnect()
    {
        $hostname = $this->host->getSshHostname();
        $username = $this->host->getSshUsername();

        if ($this->host->getSshTunnelHostname() !== null) {
            $tunnelHostname = $this->host->getSshTunnelHostname();
            $tunnelUsername = $this->host->getSshTunnelUsername();
            $connection = $this->_sshConnect($tunnelHostname, $tunnelUsername);
            ssh2_tunnel($connection, $hostname, 22);
            return $connection;
//            return $this->_sshConnect($hostname, $username);
        } else {
            return $this->_sshConnect($hostname, $username);
        }
    }

    /**
     * @param string $hostname
     * @param string $username
     * @return resource
     */
    protected function _sshConnect(string $hostname, string $username)
    {
        $methods = null;
        $useKeys = $this->host->getSshPubkeyPath() !== null;
        if ($useKeys) {
            $pubkeyPath = $this->host->getSshPubkeyPath();
            $privkeyPath = $this->host->getSshPrivkeyPath();
            Assert::readable($pubkeyPath);
            Assert::readable($privkeyPath);
            $pubkeyPath = realpath($pubkeyPath);
            $privkeyPath = realpath($privkeyPath);
            $methods = ['hostkey'=>'ssh-rsa'];
        }

        if (! $connection = ssh2_connect($hostname, 22, $methods)) {
            throw new RuntimeException("Echec de connexion ssh");
        }
        if ($useKeys) {
            if (! ssh2_auth_pubkey_file($connection, $username, $pubkeyPath, $privkeyPath)) {
                throw new RuntimeException("Echec d'authentification avec clés");
            }
        } else {
            // todo: password auth
            $password = 'coucou';
            if (! ssh2_auth_password($connection, $username, $password)) {
                throw new RuntimeException("Echec d'authentification avec mot de passe");
            }
        }

        return $connection;
    }
}