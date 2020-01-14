<?php

namespace Application\Service\Url;

use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\These;
use Application\Entity\Db\VersionFichier;
use UnicaenApp\Exception\LogicException;

class UrlTheseService extends UrlService
{
    /**
     * @param These  $these
     * @param string $redirect URL éventuelle où rediriger ensuite
     * @return string
     */
    public function refreshTheseUrl(These $these, $redirect = null)
    {
        $options = $this->options;

        if ($redirect !== null) {
            $options = array_merge_recursive($options, ['query' => ['redirect' => $redirect] ]);
        }

        return $this->fromRoute('these/refresh-these',
            ['these' => $this->idify($these)],
            $options
        );
    }

    /**
     * @param These $these
     * @return string
     */
    public function validationPageDeCouvertureUrl(These $these)
    {
        return $this->fromRoute('these/validation-page-de-couverture',
            ['these' => $this->idify($these)],
            $this->options
        );
    }

    /**
     * @param These                 $these
     * @param NatureFichier|string  $nature
     * @param VersionFichier|string $version
     * @param bool                  $retraite
     * @param array                 $queryParams
     * @return string
     */
    public function depotFichiers(These $these, $nature, $version = null, $retraite = false, array $queryParams = [])
    {
        $nature = $this->idify($nature);

        $queryParams['nature'] = $nature;
        if ($version) {
            $queryParams['version'] = $this->idify($version);
        }
        if ($retraite !== null) {
            $queryParams['retraite'] = $retraite;
        }

        switch ($nature) {
            case NatureFichier::CODE_THESE_PDF:
                if ($retraite === true) {
                    $route = 'these/depot/these-retraitee';
                } else {
                    $route = 'these/depot/these';
                }
                break;
            case NatureFichier::CODE_FICHIER_NON_PDF:
                $route = 'these/depot/annexes';
                break;
            case NatureFichier::CODE_PV_SOUTENANCE:
            case NatureFichier::CODE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_PRE_RAPPORT_SOUTENANCE:
            case NatureFichier::CODE_DEMANDE_CONFIDENT:
            case NatureFichier::CODE_PROLONG_CONFIDENT:
            case NatureFichier::CODE_CONV_MISE_EN_LIGNE:
            case NatureFichier::CODE_AVENANT_CONV_MISE_EN_LIGNE:
                $route = 'these/depot/' . NatureFichier::toRoute($nature);
                break;
            default:
                throw new LogicException("Nature spécifiée inattendue");
                break;
        }

        return $this->fromRoute($route,
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function identiteThese(These $these)
    {
        return $this->fromRoute('these/identite',
            ['these' => $this->idify($these)],
            $this->options
        );
    }

    public function depotThese(These $these, $version = null)
    {
        $route = VersionFichier::codeEstVersionCorrigee($version) ?
            'these/depot-version-corrigee' :
            'these/depot';

        return $this->fromRoute($route,
            ['these' => $this->idify($these)],
            $this->options
        );
    }

    public function archivageThese(These $these, $version = VersionFichier::CODE_ORIG)
    {
        $route = VersionFichier::codeEstVersionCorrigee($version) ?
            'these/archivage-version-corrigee' :
            'these/archivage';

        $queryParams = [
            'version' => $this->idify($version),
        ];

        return $this->fromRoute($route,
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function testArchivabilite(These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [
            'version' => $this->idify($version),
            'annexe' => false
        ];

        return $this->fromRoute('these/archivage/test-archivabilite',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function creerVersionRetraitee(These $these, $version)
    {
        $version = $this->idify($version);
        if (!in_array($version, [VersionFichier::CODE_ARCHI, VersionFichier::CODE_ARCHI_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [
            'action' => 'creerVersionRetraitee',
            'version' => $version,
        ];

        return $this->fromRoute('these/depot/these-retraitee',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function archivabiliteThese(These $these, $version, $retraite = false)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [
            'version' => $this->idify($version),
            'annexe' => false
        ];
        if ($retraite !== null) {
            $queryParams['retraite'] = $retraite;
        }

        return $this->fromRoute('these/archivabilite-these',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function conformiteTheseRetraitee(These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [
            'version' => $this->idify($version),
        ];

        return $this->fromRoute('these/conformite-these-retraitee',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function validationFichierRetraiteUrl(These $these)
    {
        return $this->fromRoute('these/depot/validation-fichier-retraite', [
            'these' => $this->idify($these),
        ]);
    }

    public function attestationThese(These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        return $this->fromRoute('these/attestation',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function diffusionThese(These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        return $this->fromRoute('these/diffusion',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function modifierCorrecAutoriseeForceeUrl(These $these)
    {
        return $this->fromRoute('these/modifier-correction-autorisee-forcee',
            ['these' => $this->idify($these)]
        );
    }

    public function modifierMetadonneesUrl(These $these)
    {
        return $this->fromRoute('these/modifier-description',
            ['these' => $this->idify($these)]
        );
    }

    public function certifierConformiteTheseRetraiteUrl(These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ARCHI, VersionFichier::CODE_ARCHI_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        return $this->fromRoute('these/modifier-certif-conformite',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function modifierAttestationUrl(These $these, VersionFichier $version)
    {
        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        return $this->fromRoute('these/modifier-attestation',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function modifierDiffusionUrl(These $these, VersionFichier $version)
    {
        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        return $this->fromRoute('these/modifier-diffusion',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function exporterConventionMiseEnLigneUrl(These $these, VersionFichier $version)
    {
        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        return $this->fromRoute('these/exporter-convention-mise-en-ligne',
            ['these' => $this->idify($these)],
            ['query' => $queryParams]
        );
    }

    public function modifierRdvBuUrl(These $these)
    {
        return $this->fromRoute('these/modifier-rdv-bu',
            ['these' => $this->idify($these)]
        );
    }

    public function validerPageDeCouvertureUrl(These $these)
    {
        return $this->fromRoute('validation/page-de-couverture',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'valider']]
        );
    }

    public function devaliderPageDeCouvertureUrl(These $these)
    {
        return $this->fromRoute('validation/page-de-couverture',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'devalider']]
        );
    }

    public function validerRdvBuUrl(These $these)
    {
        return $this->fromRoute('validation/rdv-bu',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'valider']]
        );
    }

    public function devaliderRdvBuUrl(These $these)
    {
        return $this->fromRoute('validation/rdv-bu',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'devalider']]
        );
    }

    public function validationDepotTheseCorrigeeUrl(These $these)
    {
        return $this->fromRoute('validation/validation-depot-these-corrigee',
            ['these' => $this->idify($these)]
        );
    }

    public function validationCorrectionTheseUrl(These $these)
    {
        return $this->fromRoute('validation/validation-correction-these',
            ['these' => $this->idify($these)]
        );
    }

    public function validerDepotTheseCorrigeeUrl(These $these)
    {
        return $this->fromRoute('validation/modifier-validation-depot-these-corrigee',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'valider']]
        );
    }

    public function devaliderDepotTheseCorrigeeUrl(These $these)
    {
        return $this->fromRoute('validation/modifier-validation-depot-these-corrigee',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'devalider']]
        );
    }

    public function validerCorrectionTheseUrl(These $these)
    {
        return $this->fromRoute('validation/modifier-validation-correction-these',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'valider']]
        );
    }

    public function devaliderCorrectionTheseUrl(These $these)
    {
        return $this->fromRoute('validation/modifier-validation-correction-these',
            ['these' => $this->idify($these)],
            ['query' => ['action' => 'devalider']]
        );
    }

    public function recupererFusion($these, $outputFilePath)
    {
        return $this->fromRoute('fichier/these/recuperer-fusion',
            [
                'these' => $this->idify($these),
                'outputFile' => trim(strrchr($outputFilePath, '/'), '/'),
            ]
        );
    }
}