<?php

namespace Depot\Dispatch\Depot;

use Application\Service\Url\UrlService;
use Depot\Controller\DepotController;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use Laminas\Mvc\Controller\AbstractActionController;
use These\Entity\Db\These;
use UnicaenApp\Exception\LogicException;

class DispatchService extends UrlService
{
    public function depotFichiersDispatch(AbstractActionController $controller,
                                          These $these,
                                          $nature,
                                          $version = null,
                                          bool $retraite = false,
                                          array $queryParams = []): mixed
    {
        $nature = $this->idify($nature);

        $queryParams['nature'] = $nature;
        if ($version) {
            $queryParams['version'] = $this->idify($version);
        }
        if ($retraite !== null) {
            $queryParams['retraite'] = $retraite;
        }

        switch (true) {
            case $nature === NatureFichier::CODE_THESE_PDF:
                if ($retraite === true) {
                    $route = 'these/depot/these-retraitee';
                    $action = 'these-retraitee';
                } else {
                    $route = 'these/depot/these';
                    $action = 'these';
                }
                break;
            case $nature === NatureFichier::CODE_FICHIER_NON_PDF:
                $route = 'these/depot/annexes';
                $action = 'annexes';
                break;
            case in_array($nature, NatureFichier::CODES_FICHIERS_DIVERS):
                $route = 'these/depot/divers/' . $this->getRouteNameFromNatureFichier($nature);
                $action = 'divers-' . $this->getRouteNameFromNatureFichier($nature);
                break;
            default:
                throw new LogicException("Nature de fichier spécifiée inattendue");
        }

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(DepotController::class, [
            'action' => $action,
            'these' => $this->idify($these),
            'query' => $queryParams,
        ]);
    }

    public function diffusionTheseDispatch(AbstractActionController $controller, These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(DepotController::class, [
            'action' => 'diffusion',
            'these' => $this->idify($these),
            'version' => $this->idify($version),
            'query' => $queryParams,
        ]);
    }

    public function attestationTheseDispatch(AbstractActionController $controller, These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [];
        $queryParams['version'] = $this->idify($version);

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(DepotController::class, [
            'action' => 'attestation',
            'these' => $this->idify($these),
            'query' => $queryParams,
        ]);
    }

    public function testArchivabiliteDispatch(AbstractActionController $controller, These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [
            'version' => $this->idify($version),
            'annexe' => false
        ];

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(DepotController::class, [
            'action' => 'testArchivabilite',
            'these' => $this->idify($these),
            'version' => $this->idify($version),
            'query' => $queryParams,
        ]);
    }


    public function archivabiliteTheseDispatch(AbstractActionController $controller, These $these, $version, $retraite = false)
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

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(DepotController::class, [
            'action' => 'archivabiliteThese',
            'these' => $this->idify($these),
            'query' => $queryParams,
        ]);
    }

    public function conformiteTheseRetraiteeDispatch(AbstractActionController $controller, These $these, $version)
    {
        if (!in_array($version, [VersionFichier::CODE_ORIG, VersionFichier::CODE_ORIG_CORR])) {
            throw new LogicException("Version fichier spécifiée inattendue: " . $version);
        }

        $queryParams = [
            'version' => $this->idify($version),
        ];

        /** @var \Application\Controller\Plugin\Forward $forwardPlugin */
        $forwardPlugin = $controller->forward();

        return $forwardPlugin->dispatch(DepotController::class, [
            'action' => 'conformiteTheseRetraitee',
            'these' => $this->idify($these),
            'query' => $queryParams,
        ]);
    }

    private function getRouteNameFromNatureFichier(string $code): string
    {
        return (new NatureFichier())->setCode($code)->getCodeToLowerAndDash();
    }

}