<?php

namespace Fichier\Service\VersionFichier;

use Fichier\Entity\Db\Repository\VersionFichierRepository;
use Fichier\Entity\Db\VersionFichier;
use Application\Service\BaseService;

class VersionFichierService extends BaseService
{
    /**
     * @return VersionFichierRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(VersionFichier::class);
    }

    /**
     * @param bool $estExpurge
     * @param bool $archivage
     * @param bool $corrige
     * @return VersionFichier
     * @deprecated
     */
    public function getVersionFichierForCriteria($estExpurge, $archivage, $corrige)
    {
        if ($estExpurge) {
            $version = $this->getRepository()->fetchVersionDiffusion($corrige);
        }
        elseif ($archivage) {
            $version = $this->getRepository()->fetchVersionArchivage($corrige);
        }
        else {
            $version = $this->getRepository()->fetchVersionOriginale($corrige);
        }

        return $version;
    }
}