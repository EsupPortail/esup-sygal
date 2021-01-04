<?php

namespace UnicaenDeploy\Domain;

class Host
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $sshHostname;

    /**
     * @var string
     */
    private $sshUsername;

    /**
     * @var string
     */
    private $sshPubkeyPath;

    /**
     * @var string
     */
    private $sshPrivkeyPath;

    /**
     * @var string
     */
    private $sshTunnelHostname;

    /**
     * @var string
     */
    private $sshTunnelUsername;

    /**
     * @var string
     */
    private $appdir = '/var/www/html';

    /**
     * @var string
     */
    private $barerepodir = "/var/www/sygal.git";

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Host
     */
    public function setName(string $name): Host
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSshHostname(): string
    {
        return $this->sshHostname;
    }

    /**
     * @param string $sshHostname
     * @return Host
     */
    public function setSshHostname(string $sshHostname): Host
    {
        $this->sshHostname = $sshHostname;
        return $this;
    }

    /**
     * @return string
     */
    public function getSshUsername(): string
    {
        return $this->sshUsername;
    }

    /**
     * @param string $sshUsername
     * @return Host
     */
    public function setSshUsername(string $sshUsername): Host
    {
        $this->sshUsername = $sshUsername;
        return $this;
    }

    /**
     * @return string
     */
    public function getSshPubkeyPath(): ?string
    {
        return $this->sshPubkeyPath;
    }

    /**
     * @param string|null $sshPubkeyPath
     * @return Host
     */
    public function setSshPubkeyPath(string $sshPubkeyPath = null): Host
    {
        $this->sshPubkeyPath = $sshPubkeyPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getSshPrivkeyPath(): ?string
    {
        return $this->sshPrivkeyPath;
    }

    /**
     * @param string|null $sshPrivkeyPath
     * @return Host
     */
    public function setSshPrivkeyPath(string $sshPrivkeyPath = null): Host
    {
        $this->sshPrivkeyPath = $sshPrivkeyPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getSshTunnelHostname(): ?string
    {
        return $this->sshTunnelHostname;
    }

    /**
     * @param string|null $sshTunnelHostname
     * @return Host
     */
    public function setSshTunnelHostname(string $sshTunnelHostname = null): Host
    {
        $this->sshTunnelHostname = $sshTunnelHostname;
        return $this;
    }

    /**
     * @return string
     */
    public function getSshTunnelUsername(): ?string
    {
        return $this->sshTunnelUsername;
    }

    /**
     * @param string|null $sshTunnelUsername
     * @return Host
     */
    public function setSshTunnelUsername(string $sshTunnelUsername = null): Host
    {
        $this->sshTunnelUsername = $sshTunnelUsername;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppdir(): string
    {
        return $this->appdir;
    }

    /**
     * @param string $appdir
     * @return Host
     */
    public function setAppdir(string $appdir): Host
    {
        $this->appdir = $appdir;
        return $this;
    }

    /**
     * @return string
     */
    public function getBarerepodir(): string
    {
        return $this->barerepodir;
    }

    /**
     * @param string $barerepodir
     * @return Host
     */
    public function setBarerepodir(string $barerepodir): Host
    {
        $this->barerepodir = $barerepodir;
        return $this;
    }
}