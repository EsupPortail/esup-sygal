<?php

namespace Depot\Controller\Plugin\Url;

use Depot\Service\Url\UrlDepotService;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use These\Entity\Db\These;

/**
 * Aide de vue de génération d'URL.
 *
 * @method validationPageDeCouvertureUrl(These $these)
 * @method depotFichiers(These $these, $nature, $version = null, $retraite = false, array $queryParams = [])
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
 * @see \Depot\Service\Url\UrlDepotService
 *
 * @author Unicaen
 */
class UrlDepotPlugin extends AbstractPlugin
{
    /**
     * @var \Depot\Service\Url\UrlDepotService
     */
    protected $urlDepotService;

    /**
     * @param \Depot\Service\Url\UrlDepotService $urlDepotService
     * @return self
     */
    public function setUrlDepotService(UrlDepotService $urlDepotService)
    {
        $this->urlDepotService = $urlDepotService;

        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = [])
    {
        $this->urlDepotService->setOptions($options);

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
        return call_user_func_array([$this->urlDepotService, $name], $arguments);
    }
}