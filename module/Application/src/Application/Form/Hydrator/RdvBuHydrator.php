<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\Diffusion;
use Application\Entity\Db\RdvBu;
use Application\Service\FichierThese\FichierTheseServiceAwareTrait;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class RdvBuHydrator extends DoctrineObject
{
    use FichierTheseServiceAwareTrait;
//    use EntityManagerAwareTrait;

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

//        /** @var QueryBuilder $qb */
//        $qb = $this->objectManager->getRepository(Diffusion::class)->createQueryBuilder("d")
//            ->andWhere("d.theseId = :theseId")
//            ->setParameter("theseId", $rdvBu->getThese()->getId());
//        /** @var Diffusion $result */
//        $result = $qb->getQuery()->getOneOrNullResult();

        /** @var Diffusion $result */
        $result = $this->objectManager->getRepository(Diffusion::class)->findOneBy(["these" => $rdvBu->getThese()]);


        if ($result) $data['idOrcid'] = $result->getOrcid();

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  RdvBu $rdvBu
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
        /** @var Diffusion $result */
        $result = $this->objectManager->getRepository(Diffusion::class)->findOneBy(["these" => $rdvBu->getThese()]);
        if ($result) {
            $result->setOrcid($data['idOrcid']);
            $this->objectManager->flush();
        }

        return $object;
    }
}