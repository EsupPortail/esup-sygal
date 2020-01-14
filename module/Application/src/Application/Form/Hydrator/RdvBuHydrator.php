<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RdvBu;
use Application\Entity\Db\VersionFichier;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use UnicaenApp\Exception\RuntimeException;

/**
 * @property EntityManager $objectManager
 *
 * @author Unicaen
 */
class RdvBuHydrator extends DoctrineObject
{
    use FichierTheseServiceAwareTrait;

    private function existeVersionArchivable(RdvBu $rdvBu)
    {
        return (int) $this->fichierTheseService->getRepository()->existeVersionArchivable($rdvBu->getThese());
    }

    /**
     * Extract values from an object
     *
     * @param  RdvBu $rdvBu
     * @return array
     */
    public function extract($rdvBu)
    {
        $data = parent::extract($rdvBu);
        $data['versionArchivableFournie'] = $this->existeVersionArchivable($rdvBu);

        if ($diffusion = $this->getDiffusion($rdvBu)) {
            $data['idOrcid'] = $diffusion->getOrcid();
            $data['halId'] = $diffusion->getHalId();
        }

        return $data;
    }

    /**
     * @param array  $data
     * @param object $rdvBu
     * @return RdvBu
     */
    public function hydrate(array $data, $rdvBu)
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
            try {
                $this->objectManager->flush($diffusion);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Impossible d'enregistrer la Diffusion", null, $e);
            }
        }

        return $object;
    }

    /**
     * @param RdvBu $rdvBu
     * @return Diffusion|null
     */
    private function getDiffusion(RdvBu $rdvBu)
    {
        // le RDV BU ne concerne que le dépôt de la version initiale
        $version = $this->fichierTheseService->fetchVersionFichier(VersionFichier::CODE_ORIG);

        return $rdvBu->getThese()->getDiffusionForVersion($version);
    }
}