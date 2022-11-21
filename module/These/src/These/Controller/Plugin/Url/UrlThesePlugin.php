<?php

namespace These\Controller\Plugin\Url;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use These\Entity\Db\These;
use These\Service\Url\UrlTheseService;

/**
 * Aide de vue de génération d'URL.
 *
 * @method string refreshTheseUrl(These $these, $redirect = null)
 *
 * @see UrlTheseService
 *
 * @author Unicaen
 */
class UrlThesePlugin extends AbstractPlugin
{
    /**
     * @var \These\Service\Url\UrlTheseService
     */
    protected $urlTheseService;

    /**
     * @param UrlTheseService $urlTheseService
     * @return self
     */
    public function setUrlTheseService(UrlTheseService $urlTheseService)
    {
        $this->urlTheseService = $urlTheseService;

        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = [])
    {
        $this->urlTheseService->setOptions($options);

        return $this;
    }

    /**
     * @param bool $forceCanonical
     * @return self
     */
    public function setForceCanonical($forceCanonical = true)
    {
        return $this->setOptions(['force_canonical' => $forceCanonical]);
    }

    /**
     * @param $name
     * @param $arguments
     * @return string
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->urlTheseService, $name], $arguments);
    }
}