<?php

namespace Depot\Controller;

use Application\Controller\AbstractController;
use Application\EventRouterReplacerAwareTrait;
use Application\Filter\IdifyFilterAwareTrait;
use Application\RouteMatch;
use Depot\Service\FichierHDR\FichierHDRServiceAwareTrait;
use Depot\Service\Notification\DepotNotificationFactoryAwareTrait;
use Depot\Service\These\DepotServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Fichier\Entity\Db\Fichier;
use Fichier\Service\Fichier\FichierServiceAwareTrait;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Fichier\Service\VersionFichier\VersionFichierServiceAwareTrait;
use HDR\Service\HDRServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class FichierHDRController extends AbstractController
{
    use DepotServiceAwareTrait;
    use HDRServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use FichierServiceAwareTrait;
    use FichierHDRServiceAwareTrait;
    use VersionFichierServiceAwareTrait;
    use IdifyFilterAwareTrait;
    use NotifierServiceAwareTrait;
    use DepotNotificationFactoryAwareTrait;
    use IndividuServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use EventRouterReplacerAwareTrait;

    public function telechargerFichierAction()
    {
        $fichier = $this->requestFichier();

        if (!$fichier) {
            return;
        }

        // injection préalable du contenu du fichier pour pouvoir utiliser le plugin Uploader
        try {
            $contenuFichier = $this->fichierStorageService->getFileContentForFichier($fichier);
        } catch (StorageAdapterException $e) {
            throw new RuntimeException("Impossible d'obtenir le contenu du fichier", null, $e);
        }
        $fichier->setContenuFichierData($contenuFichier);

        // Envoi du fichier au client (navigateur)
        // NB: $fichierThese->getFichier() doit être de type \UnicaenApp\Controller\Plugin\Upload\UploadedFileInterface
        $this->uploader()->download($fichier);
    }

    /**
     * @return Fichier
     */
    private function requestFichier()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getFichier();
    }
}
