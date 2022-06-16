<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RdvBu;
use Fichier\Entity\Db\VersionFichier;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @property EntityManager $objectManager
 *
 * @author Unicaen
 */
class RdvBuHydrator extends DoctrineObject
{
    use FichierTheseServiceAwareTrait;

    private function existeVersionArchivable(RdvBu $rdvBu): int
    {
        return (int) $this->fichierTheseService->getRepository()->existeVersionArchivable($rdvBu->getThese());
    }

    /**
     * Extract values from an object
     *
     * @param  RdvBu $rdvBu
     * @return array
     */
    public function extract($rdvBu): array
    {
        $data = parent::extract($rdvBu);
        $data['versionArchivableFournie'] = $this->existeVersionArchivable($rdvBu);

        if ($diffusion = $this->getDiffusion($rdvBu)) {
            $data['idOrcid'] = $diffusion->getOrcid();
            $data['halId'] = $diffusion->getHalId();
            $data['nnt'] = $diffusion->getNNT();
        }

        return $data;
    }

    /**
     * @param array $data
     * @param RdvBu $rdvBu
     * @return RdvBu
     */
    public function hydrate(array $data, $rdvBu): RdvBu
    {
        // la case à cocher "version archivable fournie" est grisée
        if (!isset($data['versionArchivableFournie'])) {
            $data['versionArchivableFournie'] = $this->existeVersionArchivable($rdvBu);
        }

        /** @var RdvBu $object */
        $object = parent::hydrate($data, $rdvBu);

        if ($diffusion = $this->getDiffusion($rdvBu)) {
            $diffusion->setOrcid($data['idOrcid']);
            $diffusion->setHalId($data['halId']);
            $diffusion->setNNT($data['nnt'] ?? null);
            try {
                $this->objectManager->flush($diffusion);
            } catch (ORMException $e) {
                throw new RuntimeException("Impossible d'enregistrer la Diffusion", null, $e);
            }
        }

        return $object;
    }

    /**
     * @param RdvBu $rdvBu
     * @return Diffusion|null
     */
    private function getDiffusion(RdvBu $rdvBu): ?Diffusion
    {
        // le RDV BU ne concerne que le dépôt de la version initiale
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        return $rdvBu->getThese()->getDiffusionForVersion($version);
    }
}