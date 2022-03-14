<?php

namespace Application\Controller\Plugin\Url;

use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use Application\Service\Url\UrlTheseService;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Aide de vue de génération d'URL.
 *
 * @method string refreshTheseUrl(These $these, $redirect = null)
 * @method validationPageDeCouvertureUrl(These $these)
 * @method depotFichiers(These $these, $nature, $version = null, $retraite = false, array $queryParams = [])
 * @method identiteThese(These $these)
 * @method depotThese(These $these, $version = null)
 * @method archivageThese(These $these, $version)
 * @method testArchivabilite(These $these, $version)
 * @method creerVersionRetraitee(These $these, $version)
 * @method archivabiliteThese(These $these, $version, $retraite = false)
 * @method conformiteTheseRetraitee(These $these, $version)
 * @method validationFichierRetraiteUrl(These $these)
 * @method attestationThese(These $these, $version)
 * @method diffusionThese(These $these, $version)
 * @method modifierMetadonneesUrl(These $these)
 * @method modifierCorrecAutoriseeForceeUrl(These $these)
 * @method accorderSursisCorrecUrl(These $these)
 * @method certifierConformiteTheseRetraiteUrl(These $these, $version)
 * @method modifierAttestationUrl(These $these, VersionFichier $version)
 * @method modifierDiffusionUrl(These $these, VersionFichier $version)
 * @method exporterConventionMiseEnLigneUrl(These $these, VersionFichier $version)
 * @method modifierRdvBuUrl(These $these)
 * @method validerRdvBuUrl(These $these)
 * @method devaliderRdvBuUrl(These $these)
 * @method validationDepotTheseCorrigeeUrl(These $these)
 * @method validationCorrectionTheseUrl(These $these)
 * @method validerDepotTheseCorrigeeUrl(These $these)
 * @method devaliderDepotTheseCorrigeeUrl(These $these)
 * @method validerCorrectionTheseUrl(These $these)
 * @method devaliderCorrectionTheseUrl(These $these)
 * @method validerPageDeCouvertureUrl(These $these)
 * @method devaliderPageDeCouvertureUrl(These $these)
 *
 * @see UrlTheseService
 *
 * @author Unicaen
 */
class UrlThesePlugin extends AbstractPlugin
{
    /**
     * @var UrlTheseService
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