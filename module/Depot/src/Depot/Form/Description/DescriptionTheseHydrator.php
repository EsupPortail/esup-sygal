<?php

namespace Depot\Form\Description;

use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use These\Entity\Db\These;

class DescriptionTheseHydrator extends DoctrineObject
{
    use DomaineHalServiceAwareTrait;

    /**
     * @param These $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [
            'domaineHal' => $object,
            'metadonneeThese' => $object
        ];

        return $data;
    }

    /**
     * @param array $data
     * @param These $object
     * @return These
     */
    public function hydrate(array $data, object $object): object
    {
        return parent::hydrate($data, $object);
    }
}