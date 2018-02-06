<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\RdvBu;
use Application\Service\Fichier\FichierServiceAwareTrait;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class RdvBuHydrator extends DoctrineObject
{
    use FichierServiceAwareTrait;


    private function existeVersionArchivable(RdvBu $rdvBu)
    {
        return (int) $this->fichierService->getRepository()->existeVersionArchivable($rdvBu->getThese());
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
        $data['pageTitreConforme'] = (int) $data['pageTitreConforme'];

        /** @var RdvBu $object */
        $object = parent::hydrate($data, $rdvBu);

        return $object;
    }
}