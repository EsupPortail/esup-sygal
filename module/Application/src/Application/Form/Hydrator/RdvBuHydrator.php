<?php

namespace Application\Form\Hydrator;

use Application\Entity\Db\RdvBu;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class RdvBuHydrator extends DoctrineObject
{
    /**
     * Extract values from an object
     *
     * @param  RdvBu $rdvBu
     * @return array
     */
    public function extract($rdvBu)
    {
        $data = parent::extract($rdvBu);

        $data['versionArchivableFournie'] = (int) $rdvBu->getThese()->existeVersionArchivable();

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
            $data['versionArchivableFournie'] = (int) $rdvBu->getThese()->existeVersionArchivable();
        }
        $data['pageTitreConforme'] = (int) $data['pageTitreConforme'];

        /** @var RdvBu $object */
        $object = parent::hydrate($data, $rdvBu);

        return $object;
    }
}